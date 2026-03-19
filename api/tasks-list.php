<?php
/**
 * タスク一覧取得 API
 *
 * GET /api/tasks-list.php
 * フロントエンドから呼び出し
 *
 * レスポンス例:
 * {
 *   "success": true,
 *   "tasks": [...],
 *   "cached_at": "2026-03-18T18:00:00+09:00"
 * }
 */

require_once dirname(__DIR__) . '/api/config.php';

set_cors_headers();

write_log('=== GET /api/tasks-list.php ===');
write_log('Method: ' . $_SERVER['REQUEST_METHOD']);

// リクエストメソッド確認
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    write_log('Error: Method not allowed');
    http_response_code(405);
    send_json(['success' => false, 'error' => 'Method not allowed. Use GET.']);
}

try {
    write_log('Starting task retrieval...');
    // API クライアント読み込み
    require_once dirname(__DIR__) . '/api/src/GoogleSheetsClient.php';
    require_once dirname(__DIR__) . '/api/src/TaskManager.php';

    // クライアント初期化
    $sheets = new GoogleSheetsClient(GOOGLE_CREDENTIALS_JSON, GOOGLE_SHEET_ID);
    $manager = new TaskManager($sheets);

    // タスク取得
    $tasks = $manager->get_all_tasks();

    // レスポンス返却
    send_json(success_response('Tasks retrieved successfully', [
        'tasks' => $tasks
    ]));

} catch (Exception $e) {
    error_log('[API Error] ' . $e->getMessage());
    http_response_code(500);
    send_json([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}
