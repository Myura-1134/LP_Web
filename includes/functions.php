<?php
/**
 * V-Link Platform - Utility Functions
 */

// セッション開始
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

// ユーザーがログインしているか確認
function is_logged_in() {
    start_session();
    return isset($_SESSION['user_id']);
}

// 現在のユーザーIDを取得
function get_current_user_id() {
    start_session();
    return $_SESSION['user_id'] ?? null;
}

// 現在のユーザー情報を取得
function get_current_user() {
    global $db;
    start_session();
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $result = $db->query("SELECT * FROM users WHERE id = $user_id");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// ログイン処理
function login_user($user_id) {
    start_session();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['login_time'] = time();
}

// ログアウト処理
function logout_user() {
    start_session();
    session_destroy();
    header('Location: ' . APP_URL . '/public/index.php');
    exit();
}

// ユーザーが主催者か確認
function is_organizer() {
    $user = get_current_user();
    return $user && ($user['user_type'] === 'organizer' || $user['user_type'] === 'both');
}

// ユーザーが参加者か確認
function is_participant() {
    $user = get_current_user();
    return $user && ($user['user_type'] === 'participant' || $user['user_type'] === 'both');
}

// ユーザー認証が必要なページへのリダイレクト
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . APP_URL . '/public/index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

// 主催者認証が必要なページへのリダイレクト
function require_organizer() {
    require_login();
    if (!is_organizer()) {
        die('このページにアクセスする権限がありません。');
    }
}

// XSS対策：HTMLエスケープ
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// SQL注入対策：プリペアドステートメント用
function prepare_statement($query, $types, $params) {
    global $db;
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->error);
    }
    $stmt->bind_param($types, ...$params);
    return $stmt;
}

// JSON レスポンス
function send_json($data, $status_code = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status_code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// エラーレスポンス
function send_error($message, $status_code = 400) {
    send_json(['error' => $message], $status_code);
}

// 成功レスポンス
function send_success($data = [], $message = 'Success') {
    send_json(['success' => true, 'message' => $message, 'data' => $data], 200);
}

// 日時フォーマット
function format_datetime($datetime) {
    return date('Y年m月d日 H:i', strtotime($datetime));
}

// 日付フォーマット
function format_date($date) {
    return date('Y年m月d日', strtotime($date));
}

// 時刻フォーマット
function format_time($time) {
    return date('H:i', strtotime($time));
}

// ランダム文字列生成
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// パスワードハッシュ化
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// パスワード検証
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// URL生成
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

// リダイレクト
function redirect($path) {
    header('Location: ' . url($path));
    exit();
}

// フラッシュメッセージ設定
function set_flash_message($key, $message) {
    start_session();
    $_SESSION['flash'][$key] = $message;
}

// フラッシュメッセージ取得
function get_flash_message($key) {
    start_session();
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// ページネーション
function paginate($total, $per_page = 10, $current_page = 1) {
    $total_pages = ceil($total / $per_page);
    $offset = ($current_page - 1) * $per_page;
    
    return [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
    ];
}
?>
