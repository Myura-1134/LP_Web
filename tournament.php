<?php
require 'db.php';

$id = $_GET['id'] ?? 0;

// 大会情報取得
$stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id=?");
$stmt->execute([$id]);
$tournament = $stmt->fetch();

if (!$tournament) {
    echo "大会が見つかりません";
    exit;
}

// エントリー処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = $_POST['team_name'];
    $members = $_POST['members']; // カンマ区切りでメンバー名
    $stmt = $pdo->prepare("INSERT INTO entries (tournament_id, team_name, members) VALUES (?, ?, ?)");
    $stmt->execute([$id, $team_name, $members]);
    header("Location: tournament.php?id=$id");
    exit;
}

// 大会エントリー取得
$stmt = $pdo->prepare("SELECT * FROM entries WHERE tournament_id=?");
$stmt->execute([$id]);
$entries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($tournament['name']) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($tournament['name']) ?></h1>
    <p><?= htmlspecialchars($tournament['description']) ?></p>
    <p>日程: <?= $tournament['date'] ?> | 形式: <?= $tournament['format'] ?></p>

    <h2>エントリー</h2>
    <form method="POST">
        <label>チーム名: <input type="text" name="team_name" required></label><br>
        <label>メンバー (カンマ区切り): <input type="text" name="members" required></label><br>
        <button type="submit">エントリー</button>
    </form>

    <h3>参加チーム一覧</h3>
    <ul>
        <?php foreach($entries as $entry): ?>
            <li><?= htmlspecialchars($entry['team_name']) ?> - <?= htmlspecialchars($entry['members']) ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="index.php">戻る</a>
</body>
</html>