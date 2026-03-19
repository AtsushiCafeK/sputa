# Phase 4: TypeScript フロント実装ガイド
ここでは既にWordPressを導入した、さくらのレンタルサーバとして説明
タスク一覧表示画面をローカルで開発し、さくらのレンタルサーバにデプロイします。

---

## ファイル構成

```
project-root/
├── package.json
├── tsconfig.json
├── vite.config.ts
├── types.ts
├── main.ts
├── index.html
├── style.css
└── node_modules/
```

---

## セットアップ手順

### ステップ 1: ローカル環境準備

#### 1-1. Node.js インストール確認

```bash
node --version  # v18.0.0 以上推奨
npm --version   # 9.0.0 以上推奨
```

未インストール時：[Node.js 公式サイト](https://nodejs.org/) からダウンロード

#### 1-2. プロジェクトフォルダ作成

```bash
mkdir sputa
cd sputa
```

#### 1-3. ファイル配置

以下のファイルをプロジェクトフォルダに配置：

```
package.json
tsconfig.json
vite.config.ts
types.ts
main.ts
index.html
style.css
```

---

### ステップ 2: 依存関係インストール

```bash
npm install
```

---

### ステップ 3: ローカル開発サーバー起動

```bash
npm run dev
```

**出力例**:
```
VITE v5.0.0  ready in 100 ms

➜  Local:   http://localhost:5173/
➜  press h to show help
```

ブラウザで `http://localhost:5173/` を開く → タスク一覧が表示される

---

### ステップ 4: ビルド



```bash
npm run build
```

ビルド結果が `public/` フォルダに出力されます：

```
public/
├── index.html
├── style.css
└── main.ts  (トランスパイル済み)
```

---

## 本番環境デプロイ

#### src/main.ts 変数にご自身のドメイン名設定

設定セクションにある次の箇所、example.comをご自身のドメインに変更してください。
const API_BASE_URL = 'https://example.com/app/api'

### ステップ 5: ビルド結果をさくらサーバにアップロード

1. `npm run build` を実行
2. FTP/ファイルマネージャーで `/public/` フォルダ内のファイルを
   さくらサーバの `/app/public/` にアップロード

```
さくらサーバ:
/app/public/
├── index.html
├── style.css
└── main.ts（またはmain.js）
```

---

### ステップ 6: 動作確認

ブラウザで以下にアクセス：

```
https://対象のDomainName/app/public/
```

**期待される結果**:
- タスク一覧画面が表示される
- 60秒ごとに自動更新される
- ステータスに応じて色分け表示される
- 進捗バーが表示される

---

## トラブルシューティング

### エラー: "npm: command not found"

**原因**: Node.js がインストールされていない

**対処法**:
1. [Node.js 公式サイト](https://nodejs.org/) をダウンロード
2. インストール
3. ターミナルを再起動して `npm --version` を確認

---

### エラー: "Cannot find module 'vite'"

**原因**: `npm install` がまだ実行されていない

**対処法**:
```bash
npm install
```

---

### ブラウザで真っ白な画面が表示される

**原因**: API から JSON 取得に失敗している可能性

**対処法**:
1. ブラウザの開発者ツール（F12）→ ネットワークタブを開く
2. API URL が正しいか確認
3. PHP API が動作しているか確認（`https://対象のDomainName/app/api/tasks-list.php`）

---

### API レスポンスが空（tasks: []）

**原因**: スプレッドシートにまだタスクが登録されていない

**対処法**:
1. Googleフォームで テスト回答を送信
2. スプレッドシート確認（新規行が追加されているか）
3. GAS ログ確認（フォーム送信トリガーが正しく動作しているか）

---

## カスタマイズ

### ポーリング間隔を変更

`main.ts` の `POLL_INTERVAL` を修正：

```typescript
const POLL_INTERVAL = 60000  // 60秒（デフォルト）
const POLL_INTERVAL = 30000  // 30秒（短くする場合）
```

---

### 色分けをカスタマイズ

`style.css` で色を変更：

```css
:root {
  --color-unstarted: #999999;   /* 未着手の色 */
  --color-working: #2563eb;     /* 対応中の色 */
  --color-completed: #10b981;   /* 完了の色 */
}
```

---

## フォルダ構成（デプロイ後）

さくらサーバ上の完全な構造：
ここでは既にWordPressを導入した、さくらのレンタルサーバとして説明

```
DomainName/
├── index.php                        # WordPress
├── wp-config.php
└── app/
    ├── api/
    │   ├── config.php
    │   ├── tasks.php
    │   ├── tasks-list.php
    │   ├── .env                     # 秘密情報
    │   └── logs.txt                 # エラーログ
    ├── src/
    │   ├── GoogleSheetsClient.php
    │   └── TaskManager.php
    ├── keys/
    │   └── service-account-key.json # Google認証ファイル
    └── public/
        ├── index.html               ← フロント
        ├── style.css                ← フロント
        └── main.js                  ← トランスパイル済み
```

---

## 次のステップ

1. ✅ Phase 1: Googleフォーム・スプレッドシート
2. ✅ Phase 2: Google Apps Script
3. ✅ Phase 3: PHP API
4. ✅ Phase 4: TypeScript フロント
5. **Phase 5: 結合テスト・本番デプロイ** ← 次

---

## 参考リソース

- [Vite Official Documentation](https://vitejs.dev/)
- [TypeScript Documentation](https://www.typescriptlang.org/)
- [MDN Web Docs - Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)
