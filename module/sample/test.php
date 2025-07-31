<?php

// このファイルはモジュール内の [モジュール名].php に記述されることを想定
// BlitZlogのコアからグローバル変数や関数が利用可能であることを前提とする

// BlitZlogコアのAPIレベル定数 (仮定)
// 実際のBlitZlogのどこかで定義されているはず
if (!defined('BLITZLOG_API_LEVEL')) {
    define('BLITZLOG_API_LEVEL', 1);
}

// モジュールの設定情報（$modules["sample"]["about"] から取得）
// 通常はここに関数を直接書くのではなく、$modules配列の外で定義し、
// それをこのクロージャ内で参照する形になることが多いです。
// 今回はサンプルとしてクロージャ内に直接記述します。
$modules["sample"]["update"] = function($id) use ($db, $Blog) { // $dbや$Blogなど、必要なグローバル変数をuseで取り込む
    
    // --- アップデート処理におけるエラーコード定義 ---
    // これらはコメントとして残しておき、実際には戻り値として使用
    /*
    00: Success!
    10: No Update
    11: Url Failed (接続、DNS解決など)
    12: Timeout
    13: SSL Error
    20: Write Error (ファイル書き込み権限など)
    21: Disk Error (ディスク容量不足)
    22: File Not Found (ダウンロードしたファイルや解凍後のファイルが見つからない)
    23: Zip Error (ZIPファイルの破損、不正な形式)
    30: Failed Update File (更新パッケージの内容が不正)
    31: API_LEVEL Error (BlitZlogコアとのAPIレベル不一致)
    32: Move Folder Error (ファイルの移動失敗)
    40: PHP Error (更新ファイルのPHP構文エラーなど)
    41: Dependencies Error (必要なPHP拡張機能が不足)
    99: other Error (その他の予期せぬエラー)
    */

    $module_name = "sample"; // このモジュールの名前
    $current_version = $GLOBALS['modules'][$module_name]['about']['version'] ?? '0.0.0';
    $update_info_url = "https://example.com/api/blitZlog/modules/{$module_name}/update_info.json"; // 更新情報取得API
    $download_url = ""; // ダウンロードURL（更新情報から取得）
    $temp_zip_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "{$module_name}_update.zip";
    $temp_extract_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "{$module_name}_update_temp";
    $module_dir = dirname(__FILE__); // 現在のモジュールディレクトリ

    // 1. 更新情報の取得
    try {
        // cURLの利用を推奨（タイムアウト、SSLエラーなどの詳細な制御のため）
        $ch = curl_init($update_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10秒でタイムアウト
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // SSL証明書の検証
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            if (strpos($curl_error, 'timed out') !== false) {
                error_log("Update error ({$module_name}): Timeout during update info fetch. " . $curl_error);
                return 12; // Timeout
            } elseif (strpos($curl_error, 'SSL certificate') !== false || strpos($curl_error, 'certificate verify failed') !== false) {
                error_log("Update error ({$module_name}): SSL Error during update info fetch. " . $curl_error);
                return 13; // SSL Error
            }
            error_log("Update error ({$module_name}): URL Failed to fetch update info. " . $curl_error);
            return 11; // Url Failed (その他接続失敗)
        }
        if ($http_code !== 200) {
            error_log("Update error ({$module_name}): HTTP error {$http_code} for update info. Response: {$response}");
            return 11; // Url Failed (HTTPエラー)
        }

        $update_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($update_data['latest_version']) || !isset($update_data['download_url']) || !isset($update_data['api_level'])) {
            error_log("Update error ({$module_name}): Failed to parse update info or missing data. JSON Error: " . json_last_error_msg());
            return 30; // Failed Update File (不正な更新情報)
        }

        $latest_version = $update_data['latest_version'];
        $download_url = $update_data['download_url'];
        $required_api_level = $update_data['api_level'];

    } catch (Throwable $e) {
        error_log("Update error ({$module_name}): Exception during update info fetch: " . $e->getMessage());
        return 99; // other Error
    }

    // 2. 更新の有無とAPIレベルのチェック
    if (version_compare($current_version, $latest_version, '>=')) {
        return 10; // No Update
    }
    if (BLITZLOG_API_LEVEL < $required_api_level) {
        error_log("Update error ({$module_name}): API_LEVEL mismatch. Core: " . BLITZLOG_API_LEVEL . ", Required: " . $required_api_level);
        return 31; // API_LEVEL Error
    }

    // 3. 更新ファイルのダウンロード
    try {
        // ディスク容量チェック（簡易的）
        // stream_context_create for timeouts without cURL:
        $context = stream_context_create([
            'http' => ['timeout' => 30], // 30秒でタイムアウト
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $file_content = @file_get_contents($download_url, false, $context);
        
        if ($file_content === false) {
            // file_get_contentsのエラーは詳細が取得しにくいが、種類を推測
            $error = error_get_last();
            if (isset($error['message']) && strpos($error['message'], 'timed out') !== false) {
                error_log("Update error ({$module_name}): Timeout during file download. " . ($error['message'] ?? ''));
                return 12; // Timeout
            }
            error_log("Update error ({$module_name}): Failed to download update file. " . ($error['message'] ?? ''));
            return 11; // Url Failed
        }

        if (file_put_contents($temp_zip_path, $file_content) === false) {
            error_log("Update error ({$module_name}): Failed to write downloaded file to temp path. Check permissions of " . sys_get_temp_dir());
            return 20; // Write Error
        }

    } catch (Throwable $e) {
        error_log("Update error ({$module_name}): Exception during file download: " . $e->getMessage());
        return 99; // other Error
    }

    // 4. ZIPファイルの解凍
    $zip = new ZipArchive;
    if ($zip->open($temp_zip_path) === true) {
        if (!is_writable($temp_extract_path) && !mkdir($temp_extract_path, 0755, true)) {
            error_log("Update error ({$module_name}): Failed to create temp extract directory or not writable: {$temp_extract_path}");
            @unlink($temp_zip_path); // 一時ファイルを削除
            return 20; // Write Error
        }
        if (!$zip->extractTo($temp_extract_path)) {
            error_log("Update error ({$module_name}): Failed to extract ZIP file: {$temp_zip_path}");
            $zip->close();
            @unlink($temp_zip_path);
            // ディスク容量不足、破損など、ZipErrorに分類
            return 23; // Zip Error
        }
        $zip->close();
    } else {
        error_log("Update error ({$module_name}): Failed to open ZIP file: {$temp_zip_path}");
        @unlink($temp_zip_path);
        return 23; // Zip Error
    }
    @unlink($temp_zip_path); // 一時ZIPファイルを削除

    // 5. 更新されたファイルの適用（既存モジュールの上書き）
    // 通常、解凍されたフォルダの直下にモジュール名と同じフォルダがあると仮定
    $extracted_module_path = $temp_extract_path . DIRECTORY_SEPARATOR . $module_name;
    if (!is_dir($extracted_module_path)) {
        error_log("Update error ({$module_name}): Extracted module directory not found: {$extracted_module_path}");
        // 念のため一時抽出フォルダも削除
        BlitZlog_rrmdir($temp_extract_path);
        return 30; // Failed Update File
    }

    // 既存モジュールをバックアップまたは削除してから、新しいモジュールを配置する
    // ここは非常にデリケートな部分なので、慎重に実装する
    try {
        // 既存のモジュールディレクトリを一時的にリネーム
        $backup_dir = $module_dir . '_old_';
        if (is_dir($module_dir) && !rename($module_dir, $backup_dir)) {
            error_log("Update error ({$module_name}): Failed to rename existing module directory for backup: {$module_dir} to {$backup_dir}");
            BlitZlog_rrmdir($temp_extract_path);
            return 32; // Move Folder Error
        }

        // 新しいモジュールを所定の場所に移動
        if (!rename($extracted_module_path, $module_dir)) {
            error_log("Update error ({$module_name}): Failed to move new module into place: {$extracted_module_path} to {$module_dir}");
            // 移動失敗時はバックアップを戻す試み
            if (is_dir($backup_dir)) {
                rename($backup_dir, $module_dir);
            }
            BlitZlog_rrmdir($temp_extract_path);
            return 32; // Move Folder Error
        }

        // 古いバックアップディレクトリを削除
        if (is_dir($backup_dir)) {
            BlitZlog_rrmdir($backup_dir);
        }

    } catch (Throwable $e) {
        error_log("Update error ({$module_name}): Exception during file application: " . $e->getMessage());
        // 致命的なエラーの場合、バックアップを戻すなどのリカバリー処理をここに入れる
        if (is_dir($backup_dir) && !is_dir($module_dir)) { // 新しいディレクトリが配置されていない場合
            rename($backup_dir, $module_dir); // バックアップを戻す
            error_log("Update error ({$module_name}): Attempted to roll back module due to application error.");
        }
        BlitZlog_rrmdir($temp_extract_path);
        return 99; // other Error
    }

    // 6. PHP構文チェック（簡易的、ただし完璧ではない）
    // 完全に安全な方法ではないため、本番環境では注意が必要
    // 理想的には、更新後にサイトがクラッシュしないことを確認するためのテストを別途行うべき
    // ここでは、更新されたモジュールファイルを手動で再require_onceしてエラーが出ないか確認するなどの方法も考えられるが、
    // サイト全体への影響があるため、慎重に
    
    // 全て成功
    BlitZlog_rrmdir($temp_extract_path); // 一時抽出フォルダをクリーンアップ
    error_log("Update success ({$module_name}): Module updated to version {$latest_version}");
    return 00; // Success!
};

// ヘルパー関数: ディレクトリとその内容を再帰的に削除する
// BlitZlogのfunction.phpなどに定義すると良いでしょう
if (!function_exists('BlitZlog_rrmdir')) {
    function BlitZlog_rrmdir($dir) {
        if (!is_dir($dir)) return true;
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir. DIRECTORY_SEPARATOR .$object))
                    BlitZlog_rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        return rmdir($dir);
    }
}