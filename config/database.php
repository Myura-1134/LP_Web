<?php
/**
 * V-Link Platform - Database Configuration
 * XAMPP MySQL接続設定
 */

// データベース接続情報
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPPのデフォルトはパスワードなし
define('DB_NAME', 'v_link_platform');
define('DB_CHARSET', 'utf8mb4');

// MySQLiで接続
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // 接続エラーチェック
    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }
    
    // 文字セット設定
    $mysqli->set_charset(DB_CHARSET);
    
} catch (Exception $e) {
    die('Database Error: ' . $e->getMessage());
}

// グローバル変数として使用可能にする
$db = $mysqli;
?>
