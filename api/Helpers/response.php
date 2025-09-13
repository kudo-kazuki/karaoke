<?php

/**
 * 共通：JSONレスポンスを返して終了
 *
 * @param mixed $data      レスポンスデータ
 * @param int   $status    HTTPステータスコード
 * @param bool  $success   成功フラグ
 * @param string|null $errorCode 任意のアプリ内エラーコード
 */
function jsonResponse(
    mixed $data,
    int $status = 200,
    bool $success = true,
    ?string $errorCode = null
): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    $body = [
        'success' => $success,
        'data'    => $data,
    ];

    if (!$success) {
        // エラーの場合のみ message/code を追加
        $body['message'] = is_string($data) ? $data : 'An error occurred';
        if ($errorCode) {
            $body['code'] = $errorCode;
        }
        unset($body['data']); // エラー時は data を外す
    }

    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    flush();
    exit;
}

/**
 * 成功時レスポンス
 *
 * 使用例：success(['id' => 1]);
 */
function success(mixed $data = null, int $status = 200): void
{
    jsonResponse($data, $status, true);
}

/**
 * エラーレスポンス
 *
 * 使用例：error('Unauthorized', 401, 'AUTH_001');
 */
function error(string $message, int $status = 400, ?string $code = null): void
{
    jsonResponse($message, $status, false, $code);
}
