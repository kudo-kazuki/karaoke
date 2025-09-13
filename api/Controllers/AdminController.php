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
            'exp' => time() + 60 * 60 * 24, // 24時間有効
        ];

        // JWT発行
        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        // 成功レスポンス
        success(['token' => $token]);
    }
}
