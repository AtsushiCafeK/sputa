# APIキー生成 - 初心者向けガイド

APIキー（認証キー）は、GASとPHPが「正しい相手と通信している」ことを確認するための暗号化された文字列です。

---

## 🎯 最も簡単な方法（推奨）

### ステップ 1: オンラインツールでランダム文字を生成

1. ブラウザで以下を開く:
   ```
   https://www.uuidgenerator.net/
   ```

2. ページを開くと、自動生成された長い英数字が表示されます
   ```
   例: a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6
   ```

3. **コピー** ボタンをクリック（またはマウスで選択してCtrl+C）

---

### ステップ 2: GAS に貼り付け

1. **Google Apps Script エディタ** を開く
2. 左側で `config.gs` をクリック
3. 以下を探す:
   ```javascript
   properties.setProperty('API_KEY', 'YOUR_API_KEY_HERE');
   ```

4. `'YOUR_API_KEY_HERE'` を削除して、貼り付けたランダム文字に置き換える
   ```javascript
   properties.setProperty('API_KEY', 'a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6');
   ```

5. **保存** ボタン（Ctrl+S）

---

### ステップ 3: PHP に貼り付け

1. **FTP/ファイルマネージャー** でさくらサーバの `/app/api/.env` を開く
   - または テキストエディタで開く（メモ帳でOK）

2. 以下を探す:
   ```
   API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

3. `xxxxxxx...` の部分を削除して、**同じランダム文字を貼り付ける**
   ```
   API_KEY=a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6
   ```

4. **保存** （ファイルメニュー → 保存、またはCtrl+S）

5. `.env` ファイルをサーバにアップロード

---

## ✅ 確認チェック

| 項目 | 確認内容 |
|---|---|
| **GAS** | `config.gs` に同じキーが設定されている |
| **PHP** | `/app/api/.env` に同じキーが設定されている |
| **スペース** | キーの前後にスペースがない |
| **完全一致** | GASとPHPで **完全に同じ文字列** |

---

## 🔄 別の方法：ツールなしで生成

**Windows の場合:**
1. スタートメニュー → 「メモ帳」を開く
2. 以下のテキストをコピー:
   ```
   aB3dE5fG7hI9jK1lM3nO5pQ7rS9tU1vW3xY5zAbCdEfGhIjKlMnOpQrStUvWxYz
   ```
3. 貼り付けて保存（好きに編集してOK）

**より安全な方法:**
- 自分で好きな組み合わせを作成（アルファベット+数字）
- 例: `MySecureKey2024_abc123xyz789_def456ghi012`
- 長いほど安全（最低20文字以上推奨）

---

## 📝 設定後の確認テスト

GASで以下を実行して、設定が正しいか確認:

1. GAS エディタで `testApiKeySettings()` を選択
2. ▶ 実行ボタンをクリック
3. ログを確認
   ```
   ✓ APIキーが設定済みです: a1b2c3d4...（一部表示）
   ```
   - ✓ が表示されれば **成功**
   - ✗ が表示されれば **再確認**

---

## ⚠️ よくあるミス

| ミス | 原因 | 対処 |
|---|---|---|
| `Unauthorized (401)` | GASとPHPのキーが異なる | 両方を確認・修正 |
| `API_KEY が設定されていません` | 'YOUR_API_KEY_HERE' のままになっている | Step 2 をやり直す |
| キー生成に失敗 | ツールサイトが開かない | 別ブラウザ試す、またはツールなし方法を使う |

---

## まとめ

```
1️⃣ オンラインツール で ランダム文字を生成
2️⃣ GAS に 貼り付け
3️⃣ PHP に 同じ文字を貼り付け
4️⃣ testApiKeySettings() で確認
```

完了です！
