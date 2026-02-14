<?php
/**
 * V-Link Platform - Home Page
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

start_session();
$user = get_current_user();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>V-Link - VALORANT大会管理プラットフォーム</title>
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
            <?php if ($user && is_organizer()): ?>
                <li><a href="create_tournament.php">大会作成</a></li>
            <?php endif; ?>
            <?php if ($user): ?>
                <li><a href="teams.php">チーム管理</a></li>
                <li><a href="dashboard.php">ダッシュボード</a></li>
                <li><a href="<?php echo API_BASE; ?>/users.php?action=logout">ログアウト</a></li>
            <?php else: ?>
                <li><a href="login.php">ログイン</a></li>
                <li><a href="register.php">登録</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- メインコンテンツ -->
    <div class="container">
        <!-- ヒーローセクション -->
        <section class="hero">
            <h1>
                <span class="bracket-left">[</span>
                <span class="glitch-text">V-LINK</span>
                <span class="bracket-right">]</span>
            </h1>
            <p>> VALORANT大会を一元管理するプラットフォーム</p>
            <p>大会情報の集約、粗相なエントリー管理、Discord連携で、アマチュア大会の運営を次のレベルへ</p>
            
            <?php if (!$user): ?>
                <div style="margin-top: 2rem;">
                    <a href="tournaments.php" class="btn">大会を探す</a>
                    <a href="register.php" class="btn" style="margin-left: 1rem;">今すぐ登録</a>
                </div>
            <?php else: ?>
                <div style="margin-top: 2rem;">
                    <a href="tournaments.php" class="btn">大会を探す</a>
                    <?php if (is_organizer()): ?>
                        <a href="create_tournament.php" class="btn" style="margin-left: 1rem;">大会を作成</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- システムステータス -->
        <section class="card">
            <div class="card-header">
                <h3>> SYSTEM STATUS</h3>
            </div>
            <div class="card-body">
                <p>
                    <span class="status-indicator online"></span>
                    Platform: OPERATIONAL
                </p>
                <p>
                    <span class="status-indicator online"></span>
                    Database: CONNECTED
                </p>
                <p>
                    <span class="status-indicator online"></span>
                    Discord API: READY
                </p>
            </div>
        </section>

        <!-- 機能紹介 -->
        <section>
            <h2>> FEATURES</h2>
            <ul class="feature-list">
                <li>
                    <strong>大会管理</strong>
                    <p>大会の作成、編集、削除を簡単に管理。シングルエリミネーション、ダブルエリミネーション、リーグ戦に対応。</p>
                </li>
                <li>
                    <strong>エントリー機能</strong>
                    <p>個人またはチーム単位でのエントリーに対応。先着順と抽選方式から選択可能。</p>
                </li>
                <li>
                    <strong>チーム管理</strong>
                    <p>チームの作成、メンバー管理、チーム情報の編集が可能。</p>
                </li>
                <li>
                    <strong>Discord連携</strong>
                    <p>Discord Webhookを通じた自動通知。エントリー確認、大会開始リマインダーなど。</p>
                </li>
                <li>
                    <strong>ダッシュボード</strong>
                    <p>主催者向けに大会管理、参加者管理、統計情報を表示。参加者向けにエントリー履歴を表示。</p>
                </li>
                <li>
                    <strong>有料プラン</strong>
                    <p>主催者プラン（月額800円）、プレミアム大会（1500円/大会）、参加者プレミアム（月額500円）。</p>
                </li>
            </ul>
        </section>

        <!-- 料金プラン -->
        <section>
            <h2>> PRICING PLANS</h2>
            <div class="grid">
                <!-- フリープラン -->
                <div class="card">
                    <div class="card-header">
                        <h3>FREE</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>¥0/月</strong></p>
                        <ul class="feature-list">
                            <li>大会への参加</li>
                            <li>チーム作成（最大1チーム）</li>
                            <li>基本的なダッシュボード</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <p style="color: var(--text-secondary);">すべてのユーザーが利用可能</p>
                    </div>
                </div>

                <!-- 主催者プラン -->
                <div class="card">
                    <div class="card-header">
                        <h3>主催者</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>¥800/月</strong></p>
                        <ul class="feature-list">
                            <li>無制限の大会作成</li>
                            <li>参加者管理機能</li>
                            <li>抽選機能</li>
                            <li>Discord連携</li>
                            <li>詳細な統計情報</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <button class="btn" onclick="alert('購入機能は準備中です')">購入</button>
                    </div>
                </div>

                <!-- プレミアプラン -->
                <div class="card">
                    <div class="card-header">
                        <h3>プレミア</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>¥500/月</strong></p>
                        <ul class="feature-list">
                            <li>プレミアム大会への優先参加</li>
                            <li>チーム数無制限</li>
                            <li>優先サポート</li>
                            <li>特別なバッジ表示</li>
                            <li>詳細な参加履歴</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <button class="btn" onclick="alert('購入機能は準備中です')">購入</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- よくある質問 -->
        <section>
            <h2>> FAQ</h2>
            <div class="card">
                <div class="card-body">
                    <h3>Q: 大会に参加するにはどうしたらいいですか？</h3>
                    <p>A: 登録してログインすると、「大会一覧」から参加したい大会を選択してエントリーできます。</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Q: 大会を主催するにはどうしたらいいですか？</h3>
                    <p>A: 登録時に「主催者」を選択するか、プロフィール設定で「主催者プラン」に登録してください。その後、「大会作成」から新しい大会を作成できます。</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3>Q: チームで参加することはできますか？</h3>
                    <p>A: はい。「チーム管理」からチームを作成し、メンバーを招待してください。大会エントリー時にチームを選択できます。</p>
                </div>
            </div>
        </section>
    </div>

    <!-- フッター -->
    <footer>
        <p>&copy; 2026 V-Link Platform. All rights reserved.</p>
        <p>VALORANT is a trademark of Riot Games, Inc.</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
