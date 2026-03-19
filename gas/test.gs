/**
 * テスト関数
 *
 * GAS エディタで手動実行してテスト
 */

/**
 * テスト1: スプレッドシート情報を確認
 */
function testSheetInfo() {
  Logger.log('=== Sheet Information ===');
  const sheet = getSheet();
  Logger.log('Sheet Name: ' + sheet.getName());
  Logger.log('Last Row: ' + sheet.getLastRow());
  Logger.log('Last Column: ' + sheet.getLastColumn());

  // ヘッダー行を表示
  const headerRow = sheet.getRange(1, 1, 1, 10).getValues()[0];
  Logger.log('Headers: ' + headerRow.join(', '));
}

/**
 * テスト2: サンプルデータを追加
 */
function testAddSampleData() {
  Logger.log('=== Adding Sample Data ===');
  addSampleData();
  Logger.log('Sample data added successfully');
}

/**
 * テスト3: 最後の行のデータを確認
 */
function testGetLastRowData() {
  Logger.log('=== Last Row Data ===');
  const sheet = getSheet();
  const lastRow = sheet.getLastRow();

  if (lastRow === 1) {
    Logger.log('No data rows (only header)');
    return;
  }

  const data = sheet.getRange(lastRow, 1, 1, 10).getValues()[0];
  Logger.log('Row ' + lastRow + ':');
  Logger.log('  Timestamp: ' + data[COLUMNS.TIMESTAMP - 1]);
  Logger.log('  Question: ' + data[COLUMNS.QUESTION - 1]);
  Logger.log('  Status: ' + data[COLUMNS.STATUS - 1]);
  Logger.log('  Progress: ' + data[COLUMNS.PROGRESS - 1]);
}

/**
 * テスト4: PHP API (tasks-list.php) への接続テスト
 */
function testPhpApiConnection() {
  Logger.log('=== PHP API Connection Test ===');

  try {
    const options = {
      method: 'get',
      muteHttpExceptions: true
    };

    const response = UrlFetchApp.fetch(PHP_API_URL, options);
    const httpCode = response.getResponseCode();

    Logger.log('HTTP Status: ' + httpCode);

    if (httpCode === 200) {
      const data = JSON.parse(response.getContentText());
      Logger.log('Response:');
      Logger.log('  Success: ' + data.success);
      Logger.log('  Tasks Count: ' + (data.tasks ? data.tasks.length : 0));
      Logger.log('  Cached At: ' + data.cached_at);

      if (data.tasks && data.tasks.length > 0) {
        Logger.log('First Task:');
        const task = data.tasks[0];
        Logger.log('  Sheet ID: ' + task.sheet_id);
        Logger.log('  Question: ' + task.question);
        Logger.log('  Status: ' + task.status);
        Logger.log('  Progress: ' + task.progress);
      }
    } else {
      Logger.log('Error: ' + response.getContentText());
    }

  } catch (error) {
    Logger.log('Exception: ' + error.toString());
  }
}

/**
 * テスト5: onEdit トリガーのシミュレーション
 */
function testOnEditSimulation() {
  Logger.log('=== onEdit Simulation ===');

  const sheet = getSheet();
  const lastRow = sheet.getLastRow();

  if (lastRow === 1) {
    Logger.log('No data rows. Run testAddSampleData() first.');
    return;
  }

  // ステータス列を編集
  const statusRange = sheet.getRange(lastRow, COLUMNS.STATUS);
  statusRange.setValue(STATUS.WORKING);
  Logger.log('Updated row ' + lastRow + ', column G (Status) to: ' + STATUS.WORKING);

  // onEdit トリガーをシミュレート
  const mockEvent = {
    range: statusRange,
    source: sheet.getParent()
  };

  onEdit(mockEvent);
}

/**
 * テスト6: すべてのテストを実行
 */
function runAllTests() {
  Logger.log('========================================');
  Logger.log('Running All Tests');
  Logger.log('========================================');

  Logger.log('\n[Test 1] Sheet Information');
  testSheetInfo();

  Logger.log('\n[Test 2] Add Sample Data');
  testAddSampleData();

  Logger.log('\n[Test 3] Get Last Row Data');
  testGetLastRowData();

  Logger.log('\n[Test 4] PHP API Connection');
  testPhpApiConnection();

  Logger.log('\n========================================');
  Logger.log('All Tests Completed');
  Logger.log('========================================');
}
