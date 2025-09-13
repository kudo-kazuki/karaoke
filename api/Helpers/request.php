<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * 共通: JSON形式のエラーレスポンス
 */
function jsonError(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * JSON入力を取得
 */
function getJsonInput(): array
{
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

/**
 * actionパラメータ取得
 */
function getAction(array $input): ?string
{
    return $input['action'] ?? null;
}

/**
 * JWT認証トークンを検証してユーザー情報を返す
 */
function getAuthenticatedUser(): object
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (!$token) {
        jsonError(401, 'Missing Authorization header');
    }

    return JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
}

/**
 * 認証済み（role問わず）でなければ401
 */
function requireAuth(): object
{
    try {
        return getAuthenticatedUser();
    } catch (Exception $e) {
        jsonError(401, 'Unauthorized');
    }
}

/**
 * 管理者専用
 */
function requireAdmin(): object
{
    $user = requireAuth();
    if (!isset($user->role) || $user->role !== 'admin') {
        jsonError(403, 'Forbidden (admin only)');
    }
    return $user;
}


/**
 * 一般ユーザー専用
 */
function requireUser(): object
{
    $user = requireAuth();
    if (!isset($user->role) || $user->role !== 'user') {
        jsonError(403, 'Forbidden (user only)');
    }
    return $user;
}
