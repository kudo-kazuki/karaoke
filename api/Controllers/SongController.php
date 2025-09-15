<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

use Models\Song;

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
}
