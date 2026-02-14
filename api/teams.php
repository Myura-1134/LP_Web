<?php
/**
 * V-Link Platform - Teams API
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            list_teams();
            break;
        case 'get':
            get_team();
            break;
        case 'create':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            create_team();
            break;
        case 'update':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            update_team();
            break;
        case 'delete':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            delete_team();
            break;
        case 'add_member':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            add_team_member();
            break;
        case 'remove_member':
            if ($method !== 'POST') {
                send_error('POST method required', 405);
            }
            remove_team_member();
            break;
        default:
            send_error('Invalid action', 400);
    }
} catch (Exception $e) {
    send_error($e->getMessage(), 500);
}

/**
 * チーム一覧取得
 */
function list_teams() {
    global $db;
    
    $leader_id = intval($_GET['leader_id'] ?? 0);
    $user_id = intval($_GET['user_id'] ?? 0);
    
    $query = "SELECT * FROM teams WHERE 1=1";
    
    if ($leader_id) {
        $query .= " AND leader_id = $leader_id";
    }
    
    if ($user_id) {
        // ユーザーが属するチームを取得
        $query = "SELECT DISTINCT t.* FROM teams t 
                  INNER JOIN team_members tm ON t.id = tm.team_id 
                  WHERE tm.user_id = $user_id OR t.leader_id = $user_id";
    }
    
    $result = $db->query($query);
    $teams = [];
    
    while ($row = $result->fetch_assoc()) {
        // メンバー情報も取得
        $members_result = $db->query(
            "SELECT u.id, u.username, tm.role FROM team_members tm 
             INNER JOIN users u ON tm.user_id = u.id 
             WHERE tm.team_id = " . $row['id']
        );
        
        $members = [];
        while ($member = $members_result->fetch_assoc()) {
            $members[] = $member;
        }
        
        $row['members'] = $members;
        $teams[] = $row;
    }
    
    send_success(['teams' => $teams]);
}

/**
 * チーム詳細取得
 */
function get_team() {
    global $db;
    
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        send_error('Team ID required', 400);
    }
    
    $result = $db->query("SELECT * FROM teams WHERE id = $id");
    
    if (!$result || $result->num_rows === 0) {
        send_error('Team not found', 404);
    }
    
    $team = $result->fetch_assoc();
    
    // メンバー情報を取得
    $members_result = $db->query(
        "SELECT u.id, u.username, tm.role FROM team_members tm 
         INNER JOIN users u ON tm.user_id = u.id 
         WHERE tm.team_id = $id"
    );
    
    $members = [];
    while ($member = $members_result->fetch_assoc()) {
        $members[] = $member;
    }
    
    $team['members'] = $members;
    
    send_success(['team' => $team]);
}

/**
 * チーム作成
 */
function create_team() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $discord_server_id = $_POST['discord_server_id'] ?? '';
    
    if (!$name) {
        send_error('Team name required', 400);
    }
    
    // チームを作成
    $stmt = $db->prepare(
        "INSERT INTO teams (name, leader_id, description, discord_server_id) 
         VALUES (?, ?, ?, ?)"
    );
    
    $stmt->bind_param('siss', $name, $user_id, $description, $discord_server_id);
    
    if (!$stmt->execute()) {
        send_error('Failed to create team: ' . $stmt->error, 500);
    }
    
    $team_id = $stmt->insert_id;
    
    // リーダーをメンバーに追加
    $stmt2 = $db->prepare(
        "INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, ?)"
    );
    
    $role = 'leader';
    $stmt2->bind_param('iis', $team_id, $user_id, $role);
    $stmt2->execute();
    
    send_success(['team_id' => $team_id], 'Team created successfully');
}

/**
 * チーム更新
 */
function update_team() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        send_error('Team ID required', 400);
    }
    
    // チームの所有者確認
    $result = $db->query("SELECT leader_id FROM teams WHERE id = $id");
    if (!$result || $result->num_rows === 0) {
        send_error('Team not found', 404);
    }
    
    $team = $result->fetch_assoc();
    if ($team['leader_id'] !== $user_id) {
        send_error('You do not have permission to update this team', 403);
    }
    
    // 更新可能なフィールド
    $updates = [];
    $params = [];
    $types = '';
    
    $fields = ['name', 'description', 'discord_server_id'];
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
    
    $params[] = $id;
    $types .= 'i';
    
    $query = "UPDATE teams SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        send_error('Failed to update team', 500);
    }
    
    send_success([], 'Team updated successfully');
}

/**
 * チーム削除
 */
function delete_team() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        send_error('Team ID required', 400);
    }
    
    // チームの所有者確認
    $result = $db->query("SELECT leader_id FROM teams WHERE id = $id");
    if (!$result || $result->num_rows === 0) {
        send_error('Team not found', 404);
    }
    
    $team = $result->fetch_assoc();
    if ($team['leader_id'] !== $user_id) {
        send_error('You do not have permission to delete this team', 403);
    }
    
    $stmt = $db->prepare("DELETE FROM teams WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        send_error('Failed to delete team', 500);
    }
    
    send_success([], 'Team deleted successfully');
}

/**
 * チームメンバー追加
 */
function add_team_member() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    $team_id = intval($_POST['team_id'] ?? 0);
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$team_id || !$member_id) {
        send_error('Team ID and member ID required', 400);
    }
    
    // チームの所有者確認
    $result = $db->query("SELECT leader_id FROM teams WHERE id = $team_id");
    if (!$result || $result->num_rows === 0) {
        send_error('Team not found', 404);
    }
    
    $team = $result->fetch_assoc();
    if ($team['leader_id'] !== $user_id) {
        send_error('You do not have permission to add members to this team', 403);
    }
    
    // メンバーが既に属しているか確認
    $existing = $db->query(
        "SELECT id FROM team_members WHERE team_id = $team_id AND user_id = $member_id"
    );
    
    if ($existing && $existing->num_rows > 0) {
        send_error('User is already a member of this team', 400);
    }
    
    // メンバーを追加
    $stmt = $db->prepare(
        "INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, ?)"
    );
    
    $role = 'member';
    $stmt->bind_param('iis', $team_id, $member_id, $role);
    
    if (!$stmt->execute()) {
        send_error('Failed to add member: ' . $stmt->error, 500);
    }
    
    send_success([], 'Member added successfully');
}

/**
 * チームメンバー削除
 */
function remove_team_member() {
    global $db;
    
    require_login();
    
    $user_id = get_current_user_id();
    $team_id = intval($_POST['team_id'] ?? 0);
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$team_id || !$member_id) {
        send_error('Team ID and member ID required', 400);
    }
    
    // チームの所有者確認
    $result = $db->query("SELECT leader_id FROM teams WHERE id = $team_id");
    if (!$result || $result->num_rows === 0) {
        send_error('Team not found', 404);
    }
    
    $team = $result->fetch_assoc();
    if ($team['leader_id'] !== $user_id) {
        send_error('You do not have permission to remove members from this team', 403);
    }
    
    // リーダーは削除できない
    if ($member_id === $team['leader_id']) {
        send_error('Cannot remove team leader', 400);
    }
    
    $stmt = $db->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $team_id, $member_id);
    
    if (!$stmt->execute()) {
        send_error('Failed to remove member', 500);
    }
    
    send_success([], 'Member removed successfully');
}
?>
