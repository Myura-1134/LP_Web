<?php
require 'db.php';

// 大会一覧取得
$stmt = $pdo->query("SELECT * FROM tournaments ORDER BY date ASC");
$tournaments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>VALORANT 大会一覧</title>
</head>
<body>
    <h1>VALORANT 大会一覧</h1>
    <a href="create_tournament.php">大会作成</a>
    <ul>
        <?php foreach($tournaments as $tournament): ?>
            <li>
                <a href="tournament.php?id=<?= $tournament['id'] ?>">
                    <?= htmlspecialchars($tournament['name']) ?> - <?= $tournament['date'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>