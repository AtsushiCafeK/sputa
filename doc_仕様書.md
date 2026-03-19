# Task Management System - 仕様書

## 1. システム概要

Google Forms で受け付けた質問を Google Sheets で管理し、Web UI でリアルタイム表示するタスク管理システム。

## 2. システム全体図

```
┌─────────────┐
│ Google Forms│ ← ユーザーが質問送信
└──────┬──────┘
       │（自動保存）
┌──────▼──────────────┐
│  Google Sheets      │ ← 進捗・コメント編集
│  （問合せ一覧）     │
└──────┬──────────────┘
       │（Google Sheets API）
┌──────▼─────────────────┐
│  PHP API Server        │ ← ビジネスロジック
│  - GoogleSheetsClient  │
│  - TaskManager         │
└──────┬─────────────────┘
       │（REST API）
┌──────▼────────────────────┐
│  Web UI                    │ ← 読み取り専用表示
│  - TypeScript + Vite       │
│  - 60秒毎に自動更新        │
└────────────────────────────┘
```

## 3. データモデル

### 3.1 Task オブジェクト

Google Sheets の 1 行を Task オブジェクトに変換

```typescript
interface Task {
  sheet_id: string;           // スプレッドシート内の行番号
  timestamp: string;          // 投稿日時（A列）
  age: string;               // 年齢（B列）
  question: string;          // 質問内容（C列）
  device: string;            // デバイス（D列）
  issue_time: string;        // 問題発生時期（E列）
  skill_level: string;       // スキルレベル（F列）
  status: string;            // ステータス（G列）
  progress: number;          // 進捗%（H列）
  article_url: string;       // 関連記事URL（I列）
  comment: string;           // コメント（J列）
}
```

## 4. Google Sheets スキーマ

| 列 | フィールド名 | 型 | 説明 |
|----|-------------|-----|------|
| A | TIMESTAMP | string | Google Forms のタイムスタンプ |
| B | AGE | string | ユーザーの年齢 |
| C | QUESTION | string | 質問内容 |
| D | DEVICE | string | デバイス種類 |
| E | ISSUE_TIME | string | 問題発生時期 |
| F | SKILL_LEVEL | string | スキルレベル |
| G | STATUS | string | ステータス |
| H | PROGRESS | number | 進捗率（0-100） |
| I | ARTICLE_URL | string | 関連記事URL |
| J | COMMENT | string | スタッフコメント |

## 5. ステータス定義

| ステータス | Web表示 | 色 | 説明 |
|-----------|--------|-----|------|
| 未着手 | ○ | グレー | 未対応 |
| 対応中 | ○ | ブルー | 対応中 |
| 完了 | ○ | グリーン | 完了 |
| 掲載終了 | ✗ 非表示 | グレー | 古い質問等で非表示 |

**重要**: 掲載終了は API で自動フィルタリング

## 6. API 仕様

### GET /api/tasks-list.php

全タスク一覧取得（掲載終了は除外）

**キャッシュ**: 60秒（JSON ファイル）

**レスポンス** (200 OK):
```json
{
  "success": true,
  "tasks": [...],
  "cached_at": "2026-03-18T20:15:47+09:00"
}
```

## 7. フロントエンド仕様

### 更新メカニズム

- **ポーリング間隔**: 60秒
- **キャッシュ無効化**: クエリパラメータにタイムスタンプ付与
- **fetch オプション**: `cache: 'no-store'`

### UI レイアウト

```
タスク一覧 | 更新: HH:MM
📝 質問箱フォーム
[情報ボックス]

日付 [ステータスバッジ] 進捗%
年齢: ... デバイス: ... 時期: ... スキル: ...
質問内容
URL（リンク）
コメント
```

## 8. セキュリティ

### 認証
- GAS ↔ PHP: API キー（Script Properties）
- PHP ↔ Sheets: Google Service Account（JSON）

### ファイル保護
```apache
<FilesMatch "\.env$|\.json$">
    Deny from all
</FilesMatch>
```

### 入力値検証
- ステータス: 4 値のみ許可
- 進捗: 0-100 の整数
- URL: URL 形式チェック

## 9. エラーハンドリング

- **ログ**: `/api/logs.txt`
- **フォーマット**: `[YYYY-MM-DD HH:MM:SS] [LEVEL] メッセージ`
- **最大サイズ**: 100MB（超過時リセット）

## 10. パフォーマンス

- API 応答時間: 3秒以内
- Web ページ: 1秒以内（キャッシュ時）
- 更新頻度: 60秒毎
- 同時接続: 10以上対応

## 11. デプロイメント

### GitHub 公開時

1. 個人データ削除（API キー、Sheet ID）
2. `.env.example` テンプレート作成
3. `.gitignore` に機密ファイル追加

### セットアップ手順

1. リポジトリクローン
2. `.env` 設定
3. Google Cloud 認証情報取得
4. `npm install && npm run build`
5. PHP ファイルをサーバーにアップロード
6. パーミッション設定

---

**最終更新**: 2026-03-18
