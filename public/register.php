<?php
/**
 * V-Link Platform - Register Page
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
    <title>登録 - V-Link</title>
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
            <li><a href="login.php">ログイン</a></li>
        </ul>
    </nav>

    <!-- メインコンテンツ -->
    <div class="container">
        <div style="max-width: 500px; margin: 4rem auto;">
            <h1 style="text-align: center; margin-bottom: 2rem;">
                <span class="bracket-left">[</span>
                <span class="glitch-text">REGISTER</span>
                <span class="bracket-right">]</span>
            </h1>

            <div class="card">
                <form id="registerForm" onsubmit="handleRegister(event)">
                    <div>
                        <label for="username">ユーザー名</label>
                        <input type="text" id="username" name="username" required minlength="3">
                    </div>

                    <div>
                        <label for="email">メールアドレス</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div>
                        <label for="password">パスワード</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>

                    <div>
                        <label for="password_confirm">パスワード確認</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>

                    <div>
                        <label for="user_type">ユーザータイプ</label>
                        <select id="user_type" name="user_type" required>
                            <option value="participant">参加者</option>
                            <option value="organizer">主催者</option>
                        </select>
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">登録</button>
                </form>

                <div style="margin-top: 2rem; text-align: center; border-top: 1px solid var(--neon-cyan); padding-top: 1rem;">
                    <p style="margin-bottom: 1rem;">既にアカウントをお持ちの場合は</p>
                    <a href="login.php" class="btn secondary">ログインページへ</a>
                </div>
            </div>

            <div class="alert alert-info" style="margin-top: 2rem;">
                <strong>> INFO</strong>
                <p>パスワードは6文字以上である必要があります。</p>
                <p>登録後、すぐにログインできます。</p>
            </div>
        </div>
    </div>

    <!-- フッター -->
    <footer>
        <p>&copy; 2026 V-Link Platform. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
    <script>
        async function handleRegister(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            const userType = document.getElementById('user_type').value;

            if (password !== passwordConfirm) {
                showError('パスワードが一致しません');
                return;
            }

            try {
                const userId = await registerUser(username, email, password, passwordConfirm, userType);
                if (userId) {
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                }
            } catch (error) {
                showError('登録に失敗しました: ' + error.message);
            }
        }
    </script>
</body>
</html>
