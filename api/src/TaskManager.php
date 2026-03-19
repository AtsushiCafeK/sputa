<?php
/**
 * タスク管理ビジネスロジック
 *
 * Google Sheets データを読み込み、キャッシュを更新
 */

class TaskManager {
    private $sheets_client;

    public function __construct($sheets_client) {
        $this->sheets_client = $sheets_client;
    }

    /**
     * すべてのタスクを取得（キャッシュ優先）
     */
    public function get_all_tasks() {
        // キャッシュを確認
        $cached_tasks = load_cache();
        if ($cached_tasks !== null) {
            return $cached_tasks;
        }

        // キャッシュなし→ Google Sheets から読み込み
        try {
            $rows = $this->sheets_client->get_all_data();
            $tasks = [];

            foreach ($rows as $index => $row) {
                $task = $this->parse_row($row, $index + 2); // 2行目以降

                if ($task) {
                    // 掲載終了のタスクは除外
                    if ($task['status'] !== '掲載終了') {
                        $tasks[] = $task;
                    }
                }
            }

            // キャッシュを保存
            save_cache($tasks);

            return $tasks;

        } catch (Exception $e) {
            error_log('[TaskManager] ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * スプレッドシートの行をタスクオブジェクトに変換
     *
     * 列構成:
     * A: タイムスタンプ
     * B: 年齢
     * C: 質問
     * D: デバイス
     * E: 発生時期
     * F: スキルレベル
     * G: ステータス
     * H: 進捗
     * I: 記事URL
     * J: コメント
     */
    private function parse_row($row, $sheet_id) {
        if (count($row) < 7) {
            return null; // データが不足している行は無視
        }

        return [
            'sheet_id' => (string)$sheet_id,
            'timestamp' => $row[0] ?? '',
            'age' => $row[1] ?? '',
            'question' => $row[2] ?? '',
            'device' => $row[3] ?? '',
            'issue_time' => $row[4] ?? '',
            'skill_level' => $row[5] ?? '',
            'status' => $row[6] ?? '未着手',
            'progress' => intval($row[7] ?? 0),
            'article_url' => $row[8] ?? '',
            'comment' => $row[9] ?? ''
        ];
    }
}
