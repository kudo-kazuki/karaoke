<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

use Models\Singer;

class SingerController
{
    /**
     * 歌手一覧を取得（認証不要）
     */
    public function list(): void
    {
        try {
            $singers = Singer::all()->toArray();
            success($singers);
        } catch (\Throwable $e) {
            error('歌手一覧の取得に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function create(): void
    {
        // JWT認証（管理者ロール）
        $user = requireAdmin();

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // バリデーション
        $errors = validate($input, Singer::$createRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        // 名前の重複チェック
        if (Singer::where('name', $input['name'])->exists()) {
            error('同じ名前の歌手がすでに存在します', 409); // 409 Conflict
        }

        try {
            $singer = new Singer();
            $singer->name = $input['name'];
            $singer->gender = $input['gender'] ?? null;
            $singer->is_group = (bool)$input['is_group'];
            $singer->debut_date = $input['debut_date'];
            $singer->description = $input['description'] ?? null;
            $singer->save();

            success([
                'id' => $singer->id,
                'name' => $singer->name,
                'gender' => $singer->gender,
                'is_group' => $singer->is_group,
                'debut_date' => $singer->debut_date,
                'description' => $singer->description,
                'created_at' => $singer->created_at,
            ]);
        } catch (\Throwable $e) {
            error('歌手の作成に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function edit(): void
    {
        // JWT認証（管理者ロール）
        $user = requireAdmin();

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // バリデーション
        $errors = validate($input, Singer::$editRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        try {
            $singer = Singer::findOrFail($input['id']);

            // 名前重複チェック（自分以外）
            if (
                Singer::where('name', $input['name'])
                    ->where('id', '!=', $input['id'])
                    ->exists()
            ) {
                error('同じ名前の歌手がすでに存在します', 409);
            }

            $singer->name = $input['name'];
            $singer->gender = $input['gender'] ?? null;
            $singer->is_group = (bool)$input['is_group'];
            $singer->debut_date = $input['debut_date'];
            $singer->description = $input['description'] ?? null;

            $singer->save();

            success([
                'id' => $singer->id,
                'name' => $singer->name,
                'gender' => $singer->gender,
                'is_group' => $singer->is_group,
                'debut_date' => $singer->debut_date,
                'description' => $singer->description,
                'updated_at' => $singer->updated_at,
            ]);
        } catch (\Throwable $e) {
            error('歌手の更新に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function delete(): void
    {
        // JWT認証（管理者ロール）
        $user = requireAdmin();

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // バリデーション
        $errors = validate($input, Singer::$deleteRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        try {
            $singer = Singer::find($input['id']);

            if (!$singer) {
                error('指定された歌手は存在しません', 404);
            }

            $singer->delete();

            // 204: No Content
            success(null, 204);
        } catch (\Throwable $e) {
            error('歌手の削除に失敗しました: ' . $e->getMessage(), 500);
        }
    }
}
