# PHP API セットアップガイド

さくらのレンタルサーバに PHP API をセットアップするための手順です。

---

## 概要

以下のファイルをさくらサーバの `/app/` フォルダ配下に配置します：

```
/app/
├── api/
│   ├── config.php          ← 設定・認証関数
│   ├── tasks.php           ← POST/PATCH エンドポイント
│   ├── tasks-list.php      ← GET エンドポイント
│   └── .env                ← 環境変数（.env.example をコピーして作成）
├── src/
│   ├── GoogleSheetsClient.php   ← Google Sheets API クライアント
│   └── TaskManager.php          ← ビジネスロジック
└── public/
    └── index.html          ← フロントエンド（後で配置）
```

---

## セットアップ手順

### ステップ 1: Google Cloud Console でサービスアカウント作成

1. [Google Cloud Console](https://console.cloud.google.com/) を開く
2. `新しいプロジェクト` を作成
3. `APIs & Services` → `Enable APIs and Services`
   - `Google Sheets API` を検索・有効化
4. `APIs & Services` → `Credentials`
   - `Create Credentials` → `Service Account`
   - 名前：`task-manager-service`
   - `Create and Continue`
5. `Grant this service account access to the project`
   - ロール：`Editor` を選択
   - `Continue` → `Done`
6. 作成したサービスアカウントをクリック
7. `Keys` タブ
   - `Add Key` → `Create new key`
   - Key type: `JSON`
   - ファイルが自動ダウンロードされる（`xxxx-yyyy-zzzz.json`）

**重要**: このJSONファイルは秘密。安全に保管してください。

---

### ステップ 2: Google スプレッドシートをサービスアカウントと共有

1. Google スプレッドシートを開く
2. `共有` をクリック
3. Step 1 で作成したサービスアカウントのメールアドレスを入力
   - 例: `task-manager-service@project-id.iam.gserviceaccount.com`
   - （JSONファイルの `client_email` 属性から取得）
4. `編集権限` を付与
5. `共有` をクリック

---

### ステップ 3: さくらサーバのフォルダ構造を作成

#### 方法 A: FTP/SFTP を使用

1. **ファイルマネージャー** または **FTP クライアント**（FileZilla等）でさくらサーバに接続
   - ホスト: `DomainName` のサーバホスト
   - ユーザー: さくらアカウント
   - パスワード: さくら設定したパスワード

2. **フォルダを作成**
   ```
   /app/
   ├── api/
   ├── src/
   └── public/
   ```

3. **各フォルダに対応するPHPファイルをアップロード**
   - `api/config.php`
   - `api/tasks.php`
   - `api/tasks-list.php`
   - `src/GoogleSheetsClient.php`
   - `src/TaskManager.php`

#### 方法 B: さくらコントロールパネルの「ファイルマネージャー」

1. さくらコントロールパネル → `ファイルマネージャー`
2. `public_html` フォルダを開く（WordPressディレクトリが表示される）
3. `新規フォルダ` で `/app/` を作成
4. `/app/` 内に `api`, `src`, `public` を作成
5. ファイルをアップロード

---

### ステップ 4: .env ファイルを作成

1. **`.env.example` をコピーして `.env` を作成**

2. **テキストエディタで編集**（FTPクライアントやファイルマネージャーから）

   ```bash
   # Google Sheets API 認証
   GOOGLE_CREDENTIALS_JSON=/home/ユーザー名/app/keys/service-account-key.json
   GOOGLE_SHEET_ID=スプレッドシートID
   API_KEY=xxxxxxxxxxxxxxxxxxxxxxxx（APIキーを設定）

   # その他設定
   CACHE_DIR=/tmp
   CACHE_TTL=60
   TIMEZONE=Asia/Tokyo
   CORS_ALLOWED_ORIGIN=https://DomainName
   LOG_LEVEL=info
   ```

3. **各項目を設定**

   | 項目 | 値 | 説明 |
   |---|---|---|
   | `GOOGLE_CREDENTIALS_JSON` | `/home/ユーザー名/app/keys/service-account-key.json` | Step 1 でダウンロードしたJSONファイルのパス |
   | `GOOGLE_SHEET_ID` | スプレッドシートURL から取得 | 例: `1a2b3c4d5e6f7g8h9i0j1k2l3m4n5o6p` |
   | `API_KEY` | 任意の長い文字列 | GASに設定したAPIキーと同じ値 |

   **GOOGLE_SHEET_IDの取得方法**:
   ```
   URL: https://docs.google.com/spreadsheets/d/1a2b3c4d5e6f7g8h9i0j1k2l3m4n5o6p/edit
                                           ↑ この部分 ↑
   ```

4. **サーバにアップロード**
   - `/app/api/.env`

5. **ファイルパーミッション設定**（セキュリティ重要）
   - ファイルパーミッション: `600` に設定
   ```bash
   chmod 600 /app/api/.env
   ```
   - **理由**: APIキーなどの秘密情報が含まれているため、所有者のみが読み取り・書き込み可能にする

---

### ステップ 5: Google 認証ファイルをサーバにアップロード

1. Step 1 でダウンロードしたJSONファイル（`xxxx-yyyy-zzzz.json`）を
   `/app/keys/` フォルダにアップロード

2. ファイル名を簡潔に：`service-account-key.json` にリネーム

3. ファイルパーミッション：`600` に設定（セキュリティ）
   ```bash
   chmod 600 /app/keys/service-account-key.json
   ```

---

### ステップ 6: GAS のAPIキー設定

1. Google Apps Script エディタで `applySettings()` を実行
2. GAS のスクリプトプロパティに `API_KEY` を設定
3. 値は Step 4 で `.env` に設定した値と **完全に一致する** ことを確認

---

### ステップ 7: テスト実行

#### テスト A: API疎通確認

1. ブラウザで以下にアクセス
   ```
   https://DomainName/app/api/tasks-list.php
   ```

2. JSONレスポンスが返ってくるか確認
   ```json
   {
     "success": true,
     "message": "Tasks retrieved successfully",
     "tasks": [],
     "cached_at": "2026-03-18T10:30:00+09:00"
   }
   ```

3. **エラーが出た場合**
   - ブラウザの開発者ツール → ネットワークタブ を確認
   - サーバのエラーログを確認（`/app/api/logs.txt`）

#### テスト B: フロントエンドからのAPI呼び出し

```javascript
// ブラウザコンソール（開発者ツール）で実行
fetch('https://DomainName/app/api/tasks-list.php')
  .then(res => res.json())
  .then(data => console.log(data));
```

---

## トラブルシューティング

### エラー: ".env file not found"

**原因**: `/app/api/.env` が作成されていない

**対処法**:
1. Step 4 に戻って `.env` を作成
2. パスが正しいか確認（`/app/api/.env`）

---

### エラー: "Credentials file not found"

**原因**: `service-account-key.json` のパスが間違っている

**対処法**:
1. `.env` で指定されたパスを確認
2. ファイルが実際に存在するか確認
3. ファイルパーミッション確認（`600`）

---

### エラー: "Unauthorized"

**原因**: APIキーが `.env` と GAS で一致していない

**対処法**:
1. `.env` で設定した `API_KEY` を確認
2. GAS のスクリプトプロパティで設定した `API_KEY` を確認
3. 完全に一致しているか確認（スペースやケースに注意）

---

### エラー: "Failed to get access token"

**原因**: Google認証ファイルが正しくない、または Google Cloud Console で設定が不完全

**対処法**:
1. Step 1 でサービスアカウントが正しく作成されているか確認
2. Step 2 でスプレッドシートがサービスアカウントと共有されているか確認
3. Google Sheets API が有効化されているか確認

---

### エラー: "Task created successfully" しかし、スプレッドシートに反映されない

**原因**: Google Sheets API の権限が不十分

**対処法**:
1. Step 2 を確認：スプレッドシートにサービスアカウントが `編集権限` で共有されているか

---

## セキュリティ上の注意

### ファイルパーミッション設定（重要）

**秘密情報を含む2つのファイルは必ず `600` に設定**:

```bash
chmod 600 /app/api/.env
chmod 600 /app/keys/service-account-key.json
```

| ファイル | パーミッション | 理由 |
|---|---|---|
| `/app/api/.env` | `600` | APIキー・スプレッドシートIDなど秘密情報が含まれている |
| `/app/keys/service-account-key.json` | `600` | Google OAuth 秘密鍵（最重要） |

**パーミッション `600` の意味**:
- 所有者（オーナー）: 読み取り・書き込み可能 ✓
- グループ: アクセス不可 ✗
- その他: アクセス不可 ✗
ただし、パーミッションだけでは外部アクセスを防げません。
仕様書.mdで説明しますがサーバ側の設定でアクセスを防ぎます。

### その他のセキュリティ対策

- ✓ `.env` ファイルは **ソースコード管理（Git）に含めない**（`.gitignore` で除外）
- ✓ `service-account-key.json` は **ソース管理に含めない**
- ✓ `API_KEY` は **十分なランダム性を持つ** 強力な文字列（最低64文字）
- ✓ ダウンロード後のローカル JSONファイルは **削除推奨**（安全保管後）
- ✓ 本番環境では **HTTPS** でアクセス（ドメイン は HTTPS）
- ✓ ログファイル（`/app/api/logs.txt`）で秘密情報が出力されないよう留意

---

## ログの確認

API実行時のログは `/app/api/logs.txt` に記録されます：

```bash
# サーバで確認
tail -f /app/api/logs.txt
```

または FTP/ファイルマネージャーでダウンロードして確認。

---

## 次ステップ

1. **Phase 4: TypeScript フロントエンド実装**
   - `/app/src/` でフロント画面実装
   - Vite でビルド
   - `/app/public/` に配置

---

## 参考

- [Google Sheets API Documentation](https://developers.google.com/sheets/api)
- [さくらサーバ - FTP/ファイルマネージャー](https://help.sakura.ad.jp/)
- [PHP cURL Manual](https://www.php.net/manual/en/book.curl.php)
