<?php
/**
 * V-Link Platform - Entries API
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            list_entries();
            break;
        case 'create':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            create_entry();
            break;
        case 'cancel':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            cancel_entry();
            break;
        default:
            send_error('Invalid action', 400);
    }
} catch (Exception $e) {
    send_error($e->getMessage(), 500);
}

/**
 * エントリー一覧取得
 */
function list_entries() {
    global $db;
    
    $tournament_id = intval($_GET['tournament_id'] ?? 0);
    $user_id = intval($_GET['user_id'] ?? 0);
    
    $query = "SELECT e.*, u.username, t.name as team_name FROM entries e 
              LEFT JOIN users u ON e.user_id = u.id 
              LEFT JOIN teams t ON e.team_id = t.id 
              WHERE 1=1";
    
    if ($tournament_id) {
        $query .= " AND e.tournament_id = $tournament_id";
    }
    
    if ($user_id) {
        $query .= " AND e.user_id = $user_id";
    }
    
    $result = $db->query($query);
    $entries = [];
    
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    
    send_success(['entries' => $entries]);
}

/**
 * エントリー作成
 */
function create_entry() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    $tournament_id = intval($_POST['tournament_id'] ?? 0);
    $entry_type = $_POST['entry_type'] ?? 'individual';
    $team_id = intval($_POST['team_id'] ?? 0);
    
    if (!$tournament_id) {
        send_error('Tournament ID required', 400);
    }
    
    // 大会の存在確認
    $tournament_result = $db->query("SELECT * FROM tournaments WHERE id = $tournament_id");
    if (!$tournament_result || $tournament_result->num_rows === 0) {
        send_error('Tournament not found', 404);
    }
    
    $tournament = $tournament_result->fetch_assoc();
    
    // 既にエントリーしているか確認
    $existing = $db->query(
        "SELECT id FROM entries WHERE tournament_id = $tournament_id AND user_id = $user_id"
    );
    
    if ($existing && $existing->num_rows > 0) {
        send_error('Already entered this tournament', 400);
    }
    
    // 参加者数確認
    if ($tournament['current_participants'] >= $tournament['max_participants']) {
        send_error('Tournament is full', 400);
    }
    
    // チームエントリーの場合、チームの存在確認
    if ($entry_type === 'team' && $team_id) {
        $team_result = $db->query("SELECT * FROM teams WHERE id = $team_id");
        if (!$team_result || $team_result->num_rows === 0) {
            send_error('Team not found', 404);
        }
    }
    
    // エントリーを作成
    $stmt = $db->prepare(
        "INSERT INTO entries (tournament_id, user_id, team_id, entry_type, status) 
         VALUES (?, ?, ?, ?, ?)"
    );
    
    $status = 'confirmed';
    $team_id_val = $entry_type === 'team' ? $team_id : null;
    
    $stmt->bind_param(
        'iisis',
        $tournament_id, $user_id, $team_id_val, $entry_type, $status
    );
    
    if (!$stmt->execute()) {
        send_error('Failed to create entry: ' . $stmt->error, 500);
    }
    
    // 大会の参加者数を更新
    $db->query(
        "UPDATE tournaments SET current_participants = current_participants + 1 WHERE id = $tournament_id"
    );
    
    send_success(['entry_id' => $stmt->insert_id], 'Entry created successfully');
}

/**
 * エントリーキャンセル
 */
function cancel_entry() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    $entry_id = intval($_POST['entry_id'] ?? 0);
    
    if (!$entry_id) {
        send_error('Entry ID required', 400);
    }
    
    // エントリーの確認
    $result = $db->query("SELECT * FROM entries WHERE id = $entry_id");
    if (!$result || $result->num_rows === 0) {
        send_error('Entry not found', 404);
    }
    
    $entry = $result->fetch_assoc();
    
    // ユーザーの確認
    if ($entry['user_id'] !== $user_id) {
        send_error('You do not have permission to cancel this entry', 403);
    }
    
    // キャンセル済みか確認
    if ($entry['status'] === 'cancelled') {
        send_error('Entry is already cancelled', 400);
    }
    
    // ステータスを更新
    $stmt = $db->prepare("UPDATE entries SET status = ?, cancelled_at = NOW() WHERE id = ?");
    $status = 'cancelled';
    $stmt->bind_param('si', $status, $entry_id);
    
    if (!$stmt->execute()) {
        send_error('Failed to cancel entry', 500);
    }
    
    // 大会の参加者数を更新
    $db->query(
        "UPDATE tournaments SET current_participants = current_participants - 1 WHERE id = " . $entry['tournament_id']
    );
    
    send_success([], 'Entry cancelled successfully');
}
?>
