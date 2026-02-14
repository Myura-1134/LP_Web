<?php
/**
 * V-Link Platform - Users API
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            register_user();
            break;
        case 'login':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            login();
            break;
        case 'logout':
            logout_user();
            break;
        case 'get_profile':
            get_profile();
            break;
        case 'update_profile':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            update_profile();
            break;
        case 'get_current':
            get_current();
            break;
        default:
            send_error('Invalid action', 400);
    }
} catch (Exception $e) {
    send_error($e->getMessage(), 500);
}

/**
 * ユーザー登録
 */
function register_user() {
    global $db;
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $user_type = $_POST['user_type'] ?? 'participant';
    
    // バリデーション
    if (!$username || !$email || !$password) {
        send_error('Username, email, and password are required', 400);
    }
    
    if ($password !== $password_confirm) {
        send_error('Passwords do not match', 400);
    }
    
    if (strlen($password) < 6) {
        send_error('Password must be at least 6 characters', 400);
    }
    
    // ユーザー名またはメールが既に存在するか確認
    $existing = $db->query(
        "SELECT id FROM users WHERE username = '" . $db->real_escape_string($username) . "' 
         OR email = '" . $db->real_escape_string($email) . "'"
    );
    
    if ($existing && $existing->num_rows > 0) {
        send_error('Username or email already exists', 400);
    }
    
    // パスワードをハッシュ化
    $password_hash = hash_password($password);
    
    // ユーザーを作成
    $stmt = $db->prepare(
        "INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)"
    );
    
    $stmt->bind_param('ssss', $username, $email, $password_hash, $user_type);
    
    if (!$stmt->execute()) {
        send_error('Failed to register user: ' . $stmt->error, 500);
    }
    
    $user_id = $stmt->insert_id;
    
    // ログイン
    login_user($user_id);
    
    send_success(['user_id' => $user_id], 'User registered successfully');
}

/**
 * ログイン
 */
function login() {
    global $db;
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        send_error('Username and password are required', 400);
    }
    
    // ユーザーを検索
    $result = $db->query(
        "SELECT * FROM users WHERE username = '" . $db->real_escape_string($username) . "'"
    );
    
    if (!$result || $result->num_rows === 0) {
        send_error('Invalid username or password', 401);
    }
    
    $user = $result->fetch_assoc();
    
    // パスワードを検証
    if (!verify_password($password, $user['password'])) {
        send_error('Invalid username or password', 401);
    }
    
    // ログイン
    login_user($user['id']);
    
    // パスワードを除外
    unset($user['password']);
    
    send_success(['user' => $user], 'Logged in successfully');
}

/**
 * プロフィール取得
 */
function get_profile() {
    global $db;
    
    $user_id = intval($_GET['user_id'] ?? 0);
    
    if (!$user_id) {
        send_error('User ID required', 400);
    }
    
    $result = $db->query("SELECT * FROM users WHERE id = $user_id");
    
    if (!$result || $result->num_rows === 0) {
        send_error('User not found', 404);
    }
    
    $user = $result->fetch_assoc();
    unset($user['password']);
    
    send_success(['user' => $user]);
}

/**
 * プロフィール更新
 */
function update_profile() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    
    // 更新可能なフィールド
    $updates = [];
    $params = [];
    $types = '';
    
    $fields = ['email', 'valorant_rank', 'discord_id', 'discord_username'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $params[] = $_POST[$field];
            $types .= 's';
        }
    }
    
    if (empty($updates)) {
        send_error('No fields to update', 400);
    }
    
    $params[] = $user_id;
    $types .= 'i';
    
    $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        send_error('Failed to update profile', 500);
    }
    
    send_success([], 'Profile updated successfully');
}

/**
 * 現在のユーザー情報取得
 */
function get_current() {
    $user = get_current_user();
    
    if (!$user) {
        send_error('Not logged in', 401);
    }
    
    unset($user['password']);
    
    send_success(['user' => $user]);
}
?>
