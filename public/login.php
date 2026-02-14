<?php
/**
 * V-Link Platform - Login Page
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

start_session();

// 既にログインしている場合はリダイレクト
if (is_logged_in()) {
    redirect('index.php');
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - V-Link</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- ナビゲーション -->
    <nav>
        <div class="logo">
            <span class="bracket-left">[</span>V-LINK<span class="bracket-right">]</span>
        </div>
        <ul>
            <li><a href="index.php">ホーム</a></li>
            <li><a href="tournaments.php">大会一覧</a></li>
            <li><a href="register.php">登録</a></li>
        </ul>
    </nav>

    <!-- メインコンテンツ -->
    <div class="container">
        <div style="max-width: 500px; margin: 4rem auto;">
            <h1 style="text-align: center; margin-bottom: 2rem;">
                <span class="bracket-left">[</span>
                <span class="glitch-text">LOGIN</span>
                <span class="bracket-right">]</span>
            </h1>

            <div class="card">
                <form id="loginForm" onsubmit="handleLogin(event)">
                    <div>
                        <label for="username">ユーザー名</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div>
                        <label for="password">パスワード</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">ログイン</button>
                </form>

                <div style="margin-top: 2rem; text-align: center; border-top: 1px solid var(--neon-cyan); padding-top: 1rem;">
                    <p style="margin-bottom: 1rem;">アカウントをお持ちでない場合は</p>
                    <a href="register.php" class="btn secondary">登録ページへ</a>
                </div>
            </div>

            <div class="alert alert-info" style="margin-top: 2rem;">
                <strong>> INFO</strong>
                <p>テスト用アカウント：</p>
                <p>ユーザー名: testuser</p>
                <p>パスワード: password123</p>
            </div>
        </div>
    </div>

    <!-- フッター -->
    <footer>
        <p>&copy; 2026 V-Link Platform. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
    <script>
        async function handleLogin(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const user = await loginUser(username, password);
                if (user) {
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                }
            } catch (error) {
                showError('ログインに失敗しました: ' + error.message);
            }
        }
    </script>
</body>
</html>
