<?php
/**
 * 設定・ユーティリティ関数
 *
 * .env ファイルから Google Sheets 認証情報を読み込む
 */

// エラーハンドリング
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ============================================
// ログ出力
// ============================================

function write_log($message, $level = 'INFO') {
    $log_dir = dirname(__DIR__) . '/api';
    $log_file = $log_dir . '/logs.txt';

    // ログファイルが100MBを超えたらリセット
    if (file_exists($log_file) && filesize($log_file) > 104857600) {
        unlink($log_file);
    }

    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] [{$level}] {$message}\n";

    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// PHP エラーハンドラ
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    write_log("PHP Error ($errno): $errstr in $errfile:$errline", 'ERROR');
    return false;
});

// ============================================
// 環境変数読み込み
// ============================================

function load_env_file() {
    $env_file = dirname(__DIR__) . '/api/.env';

    if (!file_exists($env_file)) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'error' => '.env file not found'
        ]));
    }

    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // コメント行をスキップ
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // KEY=VALUE を解析
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// 環境変数をロード
load_env_file();

// 定数定義
define('GOOGLE_CREDENTIALS_JSON', getenv('GOOGLE_CREDENTIALS_JSON'));
define('GOOGLE_SHEET_ID', getenv('GOOGLE_SHEET_ID'));
define('CACHE_DIR', dirname(__DIR__) . '/api/cache');
define('CACHE_TTL', 60); // 60秒

// ============================================
// CORS設定
// ============================================

function set_cors_headers() {
    header('Access-Control-Allow-Origin: https://it-libero.com');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Task-Api-Key');
    header('Content-Type: application/json; charset=utf-8');
}

// ============================================
// レスポンス関数
// ============================================

function send_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function success_response($message = '', $data = []) {
    return array_merge(
        ['success' => true, 'message' => $message],
        $data,
        ['cached_at' => date('c')]
    );
}

function error_response($message, $status = 400) {
    send_json(['success' => false, 'error' => $message], $status);
}

// ============================================
// キャッシュ関数
// ============================================

function load_cache() {
    $cache_file = CACHE_DIR . '/task_cache.json';

    if (!file_exists($cache_file)) {
        return null;
    }

    $data = json_decode(file_get_contents($cache_file), true);

    // キャッシュ有効期限チェック
    if (isset($data['timestamp']) && (time() - $data['timestamp']) < CACHE_TTL) {
        error_log('[CACHE] Using cached data');
        return $data['tasks'] ?? [];
    }

    return null;
}

function save_cache($tasks) {
    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }

    $cache_file = CACHE_DIR . '/task_cache.json';
    $data = [
        'tasks' => $tasks,
        'timestamp' => time()
    ];

    file_put_contents($cache_file, json_encode($data));
    error_log('[CACHE] Saved to ' . $cache_file);
}

// ============================================
// バリデーション関数
// ============================================

function validate_status($status) {
    $valid = ['未着手', '対応中', '完了', '掲載終了'];
    return in_array($status, $valid);
}

function validate_progress($progress) {
    $num = intval($progress);
    return $num >= 0 && $num <= 100;
}

function validate_url($url) {
    if (empty($url)) return true; // 空白は許可
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
