<?php
/**
 * Google Sheets API クライアント
 *
 * サービスアカウント認証で Google Sheets にアクセス
 */

class GoogleSheetsClient {
    private $credentials_file;
    private $sheet_id;
    private $sheet_name = '問合せ一覧';

    public function __construct($credentials_file, $sheet_id) {
        $this->credentials_file = $credentials_file;
        $this->sheet_id = $sheet_id;

        if (!file_exists($credentials_file)) {
            throw new Exception('Credentials file not found: ' . $credentials_file);
        }
    }

    /**
     * サービスアカウント認証トークンを取得
     */
    private function get_access_token() {
        $credentials = json_decode(file_get_contents($this->credentials_file), true);

        if (!$credentials) {
            throw new Exception('Failed to load credentials');
        }

        // JWT ペイロード
        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        // JWT トークン生成
        $jwt = $this->create_jwt($payload, $credentials['private_key']);

        // アクセストークン取得
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://oauth2.googleapis.com/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]),
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!isset($data['access_token'])) {
            throw new Exception('Failed to get access token');
        }

        return $data['access_token'];
    }

    /**
     * JWT トークン生成
     */
    private function create_jwt($payload, $private_key) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
        $payload = json_encode($payload);

        $header_encoded = $this->base64url_encode($header);
        $payload_encoded = $this->base64url_encode($payload);

        $signature_input = $header_encoded . '.' . $payload_encoded;

        openssl_sign($signature_input, $signature, $private_key, 'sha256');
        $signature_encoded = $this->base64url_encode($signature);

        return $signature_input . '.' . $signature_encoded;
    }

    /**
     * Base64URL エンコード
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * セルを更新
     */
    public function update_cell($range, $value) {
        try {
            $access_token = $this->get_access_token();
            // レンジを URL エンコード（シート名が日本語の場合に対応）
            $encoded_range = urlencode($range);
            $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->sheet_id}/values/{$encoded_range}?valueInputOption=USER_ENTERED";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $access_token,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'values' => [[$value]]
                ])
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code !== 200) {
                write_log('[Sheets API] Failed to update cell ' . $range . ': HTTP ' . $http_code);
                throw new Exception('Failed to update cell: ' . $response);
            }

            write_log('[Sheets API] Updated ' . $range . ' = ' . $value);

        } catch (Exception $e) {
            write_log('[Sheets API Error] ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Google Sheets API からデータを取得
     */
    public function get_all_data() {
        try {
            write_log('[DEBUG] Starting get_all_data()');
            write_log('[DEBUG] Credentials file: ' . $this->credentials_file);
            write_log('[DEBUG] Sheet ID: ' . $this->sheet_id);
            write_log('[DEBUG] Sheet name: ' . $this->sheet_name);

            $access_token = $this->get_access_token();
            write_log('[DEBUG] Access token obtained');

            // シート名を URL エンコード
            $encoded_sheet_name = urlencode($this->sheet_name);
            $range = $encoded_sheet_name . '!A2:J'; // ヘッダー（1行目）を除外
            $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->sheet_id}/values/{$range}";

            write_log('[DEBUG] API URL: ' . $url);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $access_token
                ]
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            write_log('[DEBUG] HTTP Code: ' . $http_code);
            write_log('[DEBUG] cURL Error: ' . $curl_error);
            write_log('[DEBUG] Response: ' . substr($response, 0, 500));

            if ($http_code !== 200) {
                write_log('[ERROR] API returned HTTP ' . $http_code);
                write_log('[ERROR] Response body: ' . $response);
                return [];
            }

            $data = json_decode($response, true);
            write_log('[DEBUG] Decoded JSON: ' . json_encode($data));

            return $data['values'] ?? [];

        } catch (Exception $e) {
            write_log('[EXCEPTION] ' . $e->getMessage());
            write_log('[EXCEPTION] File: ' . $e->getFile());
            write_log('[EXCEPTION] Line: ' . $e->getLine());
            throw $e;
        }
    }
}
