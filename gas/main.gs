/**
 * Google Apps Script - 問合せ管理システム
 *
 * シンプル設計:
 * - Google Forms からの新規データは自動的に Google Sheets に保存
 * - GAS は最小限（テスト関数のみ）
 * - フロントエンド から直接 PHP API にデータ送信
 */

/**
 * テスト用: スプレッドシートにサンプルデータを追加
 */
function addSampleData() {
  const sheet = getSheet();

  const newRow = [
    new Date().toISOString(),
    '30〜40歳',
    'テストお問い合わせ',
    'Windows',
    '最近数日のあいだ',
    'ちょっと得意',
    '未着手',
    0,
    '',
    ''
  ];

  sheet.appendRow(newRow);
  Logger.log('✓ Sample data added');
}
