<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $date = $_POST['date'];
    $format = $_POST['format'];

    $stmt = $pdo->prepare("INSERT INTO tournaments (name, description, date, format) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $date, $format]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>大会作成</title>
</head>
<body>
    <h1>大会作成</h1>
    <form method="POST">
        <label>大会名: <input type="text" name="name" required></label><br>
        <label>説明: <textarea name="description"></textarea></label><br>
        <label>日程: <input type="datetime-local" name="date" required></label><br>
        <label>形式:
            <select name="format">
                <option value="single_elim">シングルエリミネーション</option>
                <option value="double_elim">ダブルエリミネーション</option>
                <option value="league">リーグ戦</option>
            </select>
        </label><br>
        <button type="submit">作成</button>
    </form>
    <a href="index.php">戻る</a>
</body>
</html>