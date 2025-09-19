<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

use Models\Song;
use Models\Singer;

class SongController
{
    /**
     * 曲一覧を取得（認証不要）
     */
    public function list(): void
    {
        try {
            // singer_id → id でソートして取得
            $songs = Song::orderBy('singer_id')
                ->orderBy('id')
                ->get(['id', 'singer_id', 'name', 'youtube_url'])
                ->toArray();

            // singer_idごとにグループ化
            $grouped = [];
            foreach ($songs as $song) {
                $singerId = $song['singer_id'];
                unset($song['singer_id']); // レスポンスには不要なら外す

                $grouped[$singerId][] = $song;
            }

            success($grouped);
        } catch (\Throwable $e) {
            error('曲一覧の取得に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 指定されたsinger_idに紐づく曲一覧を取得（認証不要）
     */
    public function listBySingerId(): void
    {
        $input = getJsonInput();

        // バリデーション
        if (!isset($input['singer_id']) || !is_numeric($input['singer_id'])) {
            error('singer_idは必須です（数値）', 422);
        }

        try {
            // 歌手の存在チェック
            if (!\Models\Singer::where('id', $input['singer_id'])->exists()) {
                error('指定された歌手は存在しません', 404);
            }

            $songs = Song::where('singer_id', $input['singer_id'])
                ->orderBy('id')
                ->get(['id', 'singer_id', 'name', 'youtube_url', 'release_date', 'lyrics', 'created_at', 'updated_at'])
                ->toArray();

            success($songs);
        } catch (\Throwable $e) {
            error('曲一覧の取得に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function create(): void
    {
        // 認証（管理者限定）
        $user = requireAdmin();
        if ($user->level !== 0) {
            error('管理者権限がありません', 403);
        }

        $input = getJsonInput();

        // バリデーション
        $errors = validate($input, Song::$createRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        // singer_idの存在チェック
        if (!Singer::where('id', $input['singer_id'])->exists()) {
            error('指定された歌手は存在しません', 404);
        }

        // 同じsinger_id内でのname重複チェック
        if (
            Song::where('singer_id', $input['singer_id'])
                ->where('name', $input['name'])
                ->exists()
        ) {
            error('同じ名前の曲が既に存在します', 409);
        }

        try {
            $song = new Song();
            $song->singer_id    = $input['singer_id'];
            $song->name          = $input['name'];
            $song->youtube_url   = $input['youtube_url'] ?? null; // videoId
            $song->release_date = ($input['release_date'] ?? '') !== ''
                ? $input['release_date']
                : null;
            $song->lyrics        = $input['lyrics'] ?? null;
            $song->save();

            success([
                'id'           => $song->id,
                'singer_id'    => $song->singer_id,
                'name'         => $song->name,
                'youtube_url'  => $song->youtube_url,
                'release_date' => $song->release_date,
                'lyrics'       => $song->lyrics,
                'created_at'   => $song->created_at,
            ]);
        } catch (\Throwable $e) {
            error('曲の作成に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function edit(): void
    {
        // 認証（管理者限定）
        $user = requireAdmin();
        if ($user->level !== 0) {
            error('管理者権限がありません', 403);
        }

        $input = getJsonInput();

        // バリデーション
        $errors = validate($input, Song::$editRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        try {
            // 曲の存在チェック
            $song = Song::findOrFail($input['id']);

            // singer_idの存在チェック
            if (!Singer::where('id', $input['singer_id'])->exists()) {
                error('指定された歌手は存在しません', 404);
            }

            // 同じsinger_id内でのname重複チェック（自分自身以外）
            $exists = Song::where('singer_id', $input['singer_id'])
                ->where('name', $input['name'])
                ->where('id', '!=', $input['id'])
                ->exists();
            if ($exists) {
                error('同じ名前の曲が既に存在します', 409);
            }

            // 更新
            $song->singer_id    = $input['singer_id'];
            $song->name          = $input['name'];
            $song->youtube_url   = $input['youtube_url'] ?? null;
            $song->release_date  = ($input['release_date'] ?? '') !== ''
                ? $input['release_date']
                : null;
            $song->lyrics        = $input['lyrics'] ?? null;

            $song->save();

            success([
                'id'           => $song->id,
                'singer_id'    => $song->singer_id,
                'name'         => $song->name,
                'youtube_url'  => $song->youtube_url,
                'release_date' => $song->release_date,
                'lyrics'       => $song->lyrics,
                'updated_at'   => $song->updated_at,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            error('指定された曲が見つかりません', 404);
        } catch (\Throwable $e) {
            error('曲の更新に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function delete(): void
    {
        // 認証（管理者限定）
        $user = requireAdmin();
        if ($user->level !== 0) {
            error('管理者権限がありません', 403);
        }

        $input = getJsonInput();

        // バリデーション
        $errors = validate($input, Song::$deleteRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        try {
            $song = Song::findOrFail($input['id']);
            $song->delete();

            success(['id' => $input['id']]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            error('指定された曲が見つかりません', 404);
        } catch (\Throwable $e) {
            error('曲の削除に失敗しました: ' . $e->getMessage(), 500);
        }
    }
}
