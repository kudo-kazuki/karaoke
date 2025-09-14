<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

use Models\Admin;
use Firebase\JWT\JWT;

class AdminController
{
    public function list()
    {
        return Admin::all()->toArray();
    }

    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = $input['name'] ?? '';
        $password = $input['password'] ?? '';

        if ($name === '' || $password === '') {
            error('nameとpasswordは必須です', 422);
        }

        // 管理者をEloquentで取得
        $admin = Admin::where('name', $name)->first();

        if (!$admin || !password_verify($password, $admin->password)) {
            error('認証に失敗しました', 401);
        }

        // JWTペイロード作成
        $payload = [
            'sub' => $admin->id,
            'name' => $admin->name,
            'level' => $admin->level,
            'role' => 'admin',
            'exp' => time() + 60 * 60 * 120, // 120時間有効
        ];

        // JWT発行
        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        // 成功レスポンス
        success(['token' => $token]);
    }

    public function create(): void
    {
        // JWT認証＆管理者ロールチェック
        $user = requireAdmin();
        if ($user->level !== 0) {
            error('管理者権限がありません', 403);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // バリデーション
        $errors = validate($input, Admin::$createRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        // 名前の重複チェック
        if (Admin::where('name', $input['name'])->exists()) {
            error('同じ名前の管理者がすでに存在します', 409); // 409 Conflict
        }

        try {
            $admin = new Admin();
            $admin->name = $input['name'];
            $admin->level = (int) $input['level'];
            $admin->remarks = $input['remarks'] ?? '';
            $admin->password = password_hash($input['password'], PASSWORD_DEFAULT);
            $admin->save();

            success([
                'id' => $admin->id,
                'name' => $admin->name,
                'level' => $admin->level,
                'remarks' => $admin->remarks,
                'created_at' => $admin->created_at,
            ]);
        } catch (\Throwable $e) {
            error('管理者の作成に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function edit(): void
    {
        // JWT認証＆管理者チェック
        $user = requireAdmin();
        if ($user->level !== 0) {
            error('管理者権限がありません', 403);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // バリデーション
        $errors = validate($input, Admin::$editRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        try {
            $admin = Admin::findOrFail($input['id']);

            // 同じ名前が存在するかチェック
            $exists = Admin::where('name', $input['name'])
                ->where('id', '!=', $input['id'])
                ->exists();
            if ($exists) {
                error('同じ名前の管理者がすでに存在します', 409);
            }

            $admin->name = $input['name'];
            $admin->level = (int) $input['level'];
            $admin->remarks = $input['remarks'] ?? '';

            // パスワードが送られてきていて空でなければ更新
            if (!empty($input['password'])) {
                $admin->password = password_hash($input['password'], PASSWORD_DEFAULT);
            }

            $admin->save();

            success([
                'id' => $admin->id,
                'name' => $admin->name,
                'level' => $admin->level,
                'remarks' => $admin->remarks,
                'updated_at' => $admin->updated_at,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            error('指定された管理者が見つかりません', 404);
        } catch (\Throwable $e) {
            error('管理者の更新に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    public function delete(): void
    {
        // JWT認証＆管理者チェック
        $user = requireAdmin();
        if ($user->level !== 0) {
            error('管理者権限がありません', 403);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // バリデーション
        $errors = validate($input, Admin::$deleteRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
        }

        // 自分自身を削除しようとしていないかチェック
        if ((int)$input['id'] === (int)$user->sub) {
            error('自分自身を削除することはできません', 403);
        }

        try {
            $admin = Admin::findOrFail($input['id']);

            $admin->delete();

            success(['id' => $input['id']]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            error('指定された管理者が見つかりません', 404);
        } catch (\Throwable $e) {
            error('管理者の削除に失敗しました: ' . $e->getMessage(), 500);
        }
    }
}
