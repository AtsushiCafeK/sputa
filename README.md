# Sputa - Task Management System

Google Forms + Google Sheets + Web UI を連携した タスク管理システム

パソコンやスマホのトラブル・使い方に関する質問を Google Forms で受け付け、進捗状況を Web ページでリアルタイム表示しながら対応・回答していくシステムです。

変更など行なって使う時は、README.mdやその他仕様書などのmdファイルをAIエージェントへ与えれば把握して変更してくれると思います。

## 機能

- **質問受付**: Google Forms で質問を受け付け
- **自動保存**: 回答が Google Sheets に自動保存
- **進捗表示**: Web ページで全タスクと進捗状況をリアルタイム表示
- **ステータス管理**: 未着手・対応中・完了・掲載終了 の 4 段階ステータス
- **掲載終了対応**: 掲載終了ステータスは自動的に Web ページから非表示
- **キャッシュ無効**: 常に最新データを表示（ブラウザキャッシュを無効化）
- **モダンUI**: レスポンシブ対応のスタイリッシュなデザイン

## システム構成

```
Google Forms
    ↓（自動保存）
Google Sheets
    ↓（API連携）
PHP API Server
    ↓（REST API）
Web UI（TypeScript + Vite）
```

### アーキテクチャ

- **フロントエンド**: TypeScript + Vite（読み取り専用表示）
- **バックエンド**: PHP + Google Sheets API
- **認証**: Google Service Account（サーバー側）
- **ポーリング**: 60秒毎に自動更新

## セットアップ

### 前提条件

- Google Cloud プロジェクト
- Google Sheets API 有効化
- PHP 7.4 以上（共有レンタルサーバー対応）
- Node.js 16 以上（フロントエンドビルド用）

### 1. Google Cloud セットアップ

1. [Google Cloud Console](https://console.cloud.google.com/) で新規プロジェクト作成
2. Google Sheets API を有効化
3. サービスアカウント作成（JSON キーをダウンロード）

### 2. Google Forms & Sheets

1. Google Forms で質問フォーム作成
2. 自動的に Google Sheets に回答を保存（フォーム作成時に自動作成）
3. Sheets のカラム構成：
   - A: タイムスタンプ
   - B: 年齢
   - C: 質問
   - D: デバイス
   - E: 発生時期
   - F: スキルレベル
   - G: ステータス
   - H: 進捗（%）
   - I: 記事 URL
   - J: コメント

### 3. Google Apps Script（GAS）セットアップ

1. Google Sheets で「拡張機能 → Apps Script」を開く
2. `config.gs` と `main.gs` をコピー
3. Script Properties に `API_KEY` を設定（任意の複雑な文字列）

### 4. PHP API セットアップ

サーバー上の `/app/api/` に以下をアップロード：

```
api/
├── config.php
├── tasks-list.php
├── tasks.php
├── src/
│   ├── GoogleSheetsClient.php
│   └── TaskManager.php
├── cache/（ディレクトリ）
└── .env
```

`.env` ファイル（テンプレート）:
```env
GOOGLE_CREDENTIALS_JSON=../keys/service-account-key.json
GOOGLE_SHEET_ID=<Your Sheets ID>
```

大事なセキュリティ サーバー保護（`.htaccess`）:
```apache
<FilesMatch "\.env$|\.json$">
    Deny from all
</FilesMatch>
```
この.htaccessを角ディレクトリへ設置してAPI Keyや.envなど、漏洩したくないファイルへのアクセスを防ぎます。

### 5. フロントエンド デプロイ

#### src/main.ts 変数にご自身のドメイン名設定

設定セクションにある次の箇所、example.comをご自身のドメインに変更してください。
const API_BASE_URL = 'https://example.com/app/api'


#### デプロイ

```bash
npm install
npm run build
```

生成された `public/` フォルダをサーバーの `/app/public/` にアップロード

### 6. キャッシュディレクトリ権限

```bash
chmod 755 /app/api/cache
```

## 使用方法

### ユーザー（質問者）
1. 質問箱フォームから質問を送信
2. Web ページで進捗と回答を確認

### 管理者（回答者）
1. Google Sheets でタスク情報（進捗、コメント等）を編集
2. ステータスを「未着手」→「対応中」→「完了」と更新。（掲載終了にすると閲覧ページから消える）
3. 不要になったタスクは「掲載終了」に変更（自動的に Web から非表示）

## API エンドポイント

### GET /api/tasks-list.php
全タスク一覧を取得（掲載終了は除外）

**レスポンス**:
```json
{
  "success": true,
  "tasks": [
    {
      "sheet_id": "2",
      "timestamp": "2026/03/18 17:34:09",
      "age": "20歳以下",
      "question": "Windowsが起動しません",
      "device": "Windows",
      "issue_time": "最近数日のあいだ",
      "skill_level": "苦手",
      "status": "対応中",
      "progress": 30,
      "article_url": "https://example.com/article1",
      "comment": "解決方法を調査中です"
    }
  ],
  "cached_at": "2026-03-18T20:15:47+09:00"
}
```

## ファイル構成

```
project/
├── public/                          # Web ページ（サーバー公開）
│   ├── index.html
│   ├── assets/
│   │   ├── index.js                # フロントエンド JavaScript
│   │   └── index.css               # スタイルシート
│   └── .htaccess                   # セキュリティ設定
│
├── api/                             # PHP API（サーバー側）
│   ├── config.php                   # 設定・ユーティリティ
│   ├── tasks-list.php               # タスク取得 API
│   ├── tasks.php                    # タスク更新 API
│   ├── src/
│   │   ├── GoogleSheetsClient.php   # Google Sheets API クライアント
│   │   └── TaskManager.php          # ビジネスロジック
│   ├── cache/                       # キャッシュディレクトリ
│   ├── logs.txt                     # デバッグログ
│   └── .env                         # 環境変数（.env.example をコピー）
│
├── src/                             # ソースコード（開発用）
│   ├── main.ts                      # メイン実装
│   ├── types.ts                     # 型定義
│   └── style.css                    # スタイル（開発用）
│
├── config.gs                        # Google Apps Script 設定
├── main.gs                          # GAS メイン（現在は不使用）
├── appscript.json                   # GAS マニフェスト
│
├── vite.config.ts                   # Vite 設定
├── tsconfig.json                    # TypeScript 設定
├── package.json                     # npm パッケージ設定
│
└── README.md                        # このファイル
```

## 環境変数

`.env` ファイルに記載（サーバーで `/app/api/.env` に配置）:

| 変数 | 説明 | 例 |
|------|------|-----|
| `GOOGLE_CREDENTIALS_JSON` | Google Service Account JSON キーのパス | `../keys/service-account-key.json` |
| `GOOGLE_SHEET_ID` | Google Sheets ID | `1lF008GjwSxUAHZP4...` |

## セキュリティ

- **Service Account**: サーバー側のみで認証（クライアント側には公開されない）
- **API キー**: GAS ↔ PHP 間の通信で検証（Script Properties に保存）
- **ファイル保護**: `.htaccess` で `.env` と JSON キーへのアクセス禁止
- **CORS**: 信頼できるオリジンのみ許可
- **キャッシュ**: 60秒毎に自動更新（常に最新データ）

## トラブルシューティング

### Web ページにタスクが表示されない

1. PHP ログを確認: `/app/api/logs.txt`
2. Google Sheets ID が正しいか確認
3. Service Account キーのパスが正しいか確認
4. Google Sheets API が有効化されているか確認

### フォーム投稿が保存されない

1. Google Forms の設定を確認（回答先 Sheets が正しいか）
2. Sheets のカラム順序が仕様通りか確認

## ライセンス

MIT License

## サポート

問題が発生した場合は、GitHub Issues で報告してください。

---

**開発**: IT-Libero
