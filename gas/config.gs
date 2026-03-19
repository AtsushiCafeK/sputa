/**
 * Google Apps Script 設定・定数
 */

// ============================================
// スプレッドシート設定
// ============================================

const SHEET_NAME = '問合せ一覧';
const SPREADSHEET_ID = SpreadsheetApp.getActiveSpreadsheet().getId();

// ============================================
// API 設定
// ============================================

const PHP_API_URL = 'https://it-libero.com/app/api';

/**
 * スクリプトプロパティから API キーを取得
 */
function getApiKey() {
  const scriptProperties = PropertiesService.getScriptProperties();
  const apiKey = scriptProperties.getProperty('API_KEY');

  if (!apiKey) {
    throw new Error('API_KEY is not set in Script Properties');
  }

  return apiKey;
}

// ============================================
// 列番号定義（A=1, B=2, ... J=10）
// ============================================

const COLUMNS = {
  TIMESTAMP: 1,    // A
  AGE: 2,          // B
  QUESTION: 3,     // C
  DEVICE: 4,       // D
  ISSUE_TIME: 5,   // E
  SKILL_LEVEL: 6,  // F
  STATUS: 7,       // G
  PROGRESS: 8,     // H
  ARTICLE_URL: 9,  // I
  COMMENT: 10      // J
};

// ============================================
// ステータス定義
// ============================================

const STATUS = {
  UNSTARTED: '未着手',
  WORKING: '対応中',
  COMPLETED: '完了',
  ARCHIVED: '掲載終了'
};

/**
 * スプレッドシートを取得
 */
function getSheet() {
  return SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAME);
}

/**
 * スプレッドシート情報をログ出力
 */
function logSheetInfo() {
  const sheet = getSheet();
  Logger.log('Sheet Name: ' + sheet.getName());
  Logger.log('Last Row: ' + sheet.getLastRow());
  Logger.log('Last Column: ' + sheet.getLastColumn());
}
