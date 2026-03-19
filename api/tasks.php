<?php
/**
 * タスク更新 API
 *
 * PATCH /api/tasks.php
 * GAS から呼び出される
 *
 * リクエスト例:
 * {
 *   "action": "update",
 *   "sheet_id": "2",
 *   "status": "対応中",
 *   "progress": 50,
 *   "comment": "進行中です",
 *   "article_url": ""
 * }
 */

require_once dirname(__DIR__) . '/api/config.php';

set_cors_headers();

// リクエストメソッド確認
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    send_json(['success' => false, 'error' => 'Method not allowed. Use PATCH.']);
}

try {
    // API キー認証
    $api_key = $_SERVER['HTTP_X_TASK_API_KEY'] ?? null;
    $valid_key = getenv('API_KEY');

    if ($api_key !== $valid_key) {
        http_response_code(401);
        send_json(['success' => false, 'error' => 'Unauthorized']);
    }

    // JSON ペイロード取得
    $payload = json_decode(file_get_contents('php://input'), true);

    if (!$payload) {
        http_response_code(400);
        send_json(['success' => false, 'error' => 'Invalid JSON payload']);
    }

    // バリデーション
    if (!isset($payload['sheet_id'])) {
        http_response_code(400);
        send_json(['success' => false, 'error' => 'sheet_id is required']);
    }

    // API クライアント読み込み
    require_once dirname(__DIR__) . '/api/src/GoogleSheetsClient.php';

    // Google Sheets に直接書き込み
    $sheets = new GoogleSheetsClient(GOOGLE_CREDENTIALS_JSON, GOOGLE_SHEET_ID);
    $sheet_id = intval($payload['sheet_id']);

    // 各列を更新
    $sheet_name = '問合せ一覧';

    if (isset($payload['status'])) {
        $sheets->update_cell($sheet_name . '!G' . $sheet_id, $payload['status']);
    }

    if (isset($payload['progress'])) {
        $sheets->update_cell($sheet_name . '!H' . $sheet_id, intval($payload['progress']));
    }

    if (isset($payload['comment'])) {
        $sheets->update_cell($sheet_name . '!I' . $sheet_id, $payload['comment']);
    }

    if (isset($payload['article_url'])) {
        $sheets->update_cell($sheet_name . '!J' . $sheet_id, $payload['article_url']);
    }

    // キャッシュを削除（次の読み込みで最新データ取得）
    $cache_file = CACHE_DIR . '/task_cache.json';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }

    // レスポンス返却
    send_json(success_response('Task updated successfully'), 200);

} catch (Exception $e) {
    error_log('[API Error] ' . $e->getMessage());
    http_response_code(500);
    send_json([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}
