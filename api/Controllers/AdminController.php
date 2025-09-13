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
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // バリデーション
        $errors = validate($input, Admin::$createRules);
        if (!empty($errors)) {
            error(['validation' => $errors], 422);
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
}
