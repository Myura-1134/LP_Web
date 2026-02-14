<?php
/**
 * V-Link Platform - Tournaments API
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// リクエストメソッドを取得
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            list_tournaments();
            break;
        case 'get':
            get_tournament();
            break;
        case 'create':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            create_tournament();
            break;
        case 'update':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            update_tournament();
            break;
        case 'delete':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            delete_tournament();
            break;
        default:
            send_error('Invalid action', 400);
    }
} catch (Exception $e) {
    send_error($e->getMessage(), 500);
}

/**
 * 大会一覧取得
 */
function list_tournaments() {
    global $db;
    
    $status = $_GET['status'] ?? '';
    $organizer_id = $_GET['organizer_id'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = 10;
    
    $query = "SELECT * FROM tournaments WHERE 1=1";
    $params = [];
    $types = '';
    
    if ($status) {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if ($organizer_id) {
        $query .= " AND organizer_id = ?";
        $params[] = intval($organizer_id);
        $types .= 'i';
    }
    
    // 総数取得
    $count_result = $db->query(str_replace('SELECT *', 'SELECT COUNT(*) as total', $query));
    $count_row = $count_result->fetch_assoc();
    $total = $count_row['total'];
    
    // ページネーション
    $offset = ($page - 1) * $per_page;
    $query .= " ORDER BY scheduled_at DESC LIMIT $offset, $per_page";
    
    $stmt = $db->prepare($query);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tournaments = [];
    while ($row = $result->fetch_assoc()) {
        $tournaments[] = $row;
    }
    
    send_success([
        'tournaments' => $tournaments,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
        ]
    ]);
}

/**
 * 大会詳細取得
 */
function get_tournament() {
    global $db;
    
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        send_error('Tournament ID required', 400);
    }
    
    $result = $db->query("SELECT * FROM tournaments WHERE id = $id");
    
    if (!$result || $result->num_rows === 0) {
        send_error('Tournament not found', 404);
    }
    
    $tournament = $result->fetch_assoc();
    
    // エントリー情報も取得
    $entries_result = $db->query("SELECT * FROM entries WHERE tournament_id = $id");
    $entries = [];
    while ($row = $entries_result->fetch_assoc()) {
        $entries[] = $row;
    }
    $tournament['entries'] = $entries;
    
    send_success(['tournament' => $tournament]);
}

/**
 * 大会作成
 */
function create_tournament() {
    global $db;
    
    require_login();
    require_organizer();
    
    $user_id = get_current_user_id();
    
    // バリデーション
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $scheduled_at = $_POST['scheduled_at'] ?? '';
    $max_participants = intval($_POST['max_participants'] ?? 0);
    $min_rank = $_POST['min_rank'] ?? '';
    $max_rank = $_POST['max_rank'] ?? '';
    $entry_method = $_POST['entry_method'] ?? 'firstcome';
    $format = $_POST['format'] ?? 'single_elim';
    $rules = $_POST['rules'] ?? '';
    $discord_invite_url = $_POST['discord_invite_url'] ?? '';
    
    if (!$title || !$scheduled_at || $max_participants <= 0) {
        send_error('Required fields: title, scheduled_at, max_participants', 400);
    }
    
    // 大会を作成
    $stmt = $db->prepare(
        "INSERT INTO tournaments 
        (organizer_id, title, description, scheduled_at, max_participants, 
         min_rank, max_rank, entry_method, format, rules, discord_invite_url, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    $status = 'open';
    $stmt->bind_param(
        'isssisssssss',
        $user_id, $title, $description, $scheduled_at, $max_participants,
        $min_rank, $max_rank, $entry_method, $format, $rules, $discord_invite_url, $status
    );
    
    if (!$stmt->execute()) {
        send_error('Failed to create tournament: ' . $stmt->error, 500);
    }
    
    $tournament_id = $stmt->insert_id;
    
    send_success(['tournament_id' => $tournament_id], 'Tournament created successfully');
}

/**
 * 大会更新
 */
function update_tournament() {
    global $db;
    
    require_login();
    
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        send_error('Tournament ID required', 400);
    }
    
    // 大会の所有者確認
    $result = $db->query("SELECT organizer_id FROM tournaments WHERE id = $id");
    if (!$result || $result->num_rows === 0) {
        send_error('Tournament not found', 404);
    }
    
    $tournament = $result->fetch_assoc();
    if ($tournament['organizer_id'] !== get_current_user_id()) {
        send_error('You do not have permission to update this tournament', 403);
    }
    
    // 更新可能なフィールド
    $updates = [];
    $params = [];
    $types = '';
    
    $fields = ['title', 'description', 'max_participants', 'min_rank', 'max_rank', 'rules', 'discord_invite_url', 'status'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $params[] = $_POST[$field];
            $types .= is_numeric($_POST[$field]) ? 'i' : 's';
        }
    }
    
    if (empty($updates)) {
        send_error('No fields to update', 400);
    }
    
    $params[] = $id;
    $types .= 'i';
    
    $query = "UPDATE tournaments SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        send_error('Failed to update tournament', 500);
    }
    
    send_success([], 'Tournament updated successfully');
}

/**
 * 大会削除
 */
function delete_tournament() {
    global $db;
    
    require_login();
    
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        send_error('Tournament ID required', 400);
    }
    
    // 大会の所有者確認
    $result = $db->query("SELECT organizer_id FROM tournaments WHERE id = $id");
    if (!$result || $result->num_rows === 0) {
        send_error('Tournament not found', 404);
    }
    
    $tournament = $result->fetch_assoc();
    if ($tournament['organizer_id'] !== get_current_user_id()) {
        send_error('You do not have permission to delete this tournament', 403);
    }
    
    $stmt = $db->prepare("DELETE FROM tournaments WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        send_error('Failed to delete tournament', 500);
    }
    
    send_success([], 'Tournament deleted successfully');
}
?>
