<?php
/**
 * V-Link Platform - Tournaments List Page
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
    <title>大会一覧 - V-Link</title>
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
        <h1 style="margin-bottom: 2rem;">
            <span class="bracket-left">[</span>
            <span class="glitch-text">TOURNAMENTS</span>
            <span class="bracket-right">]</span>
        </h1>

        <!-- フィルタセクション -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3>> FILTERS</h3>
            </div>
            <div class="card-body">
                <div class="flex" style="gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="filterStatus">ステータス</label>
                        <select id="filterStatus" onchange="applyFilters()">
                            <option value="">すべて</option>
                            <option value="open">募集中</option>
                            <option value="closed">募集終了</option>
                            <option value="finished">終了</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label for="filterFormat">フォーマット</label>
                        <select id="filterFormat" onchange="applyFilters()">
                            <option value="">すべて</option>
                            <option value="single_elim">シングルエリミネーション</option>
                            <option value="double_elim">ダブルエリミネーション</option>
                            <option value="league">リーグ戦</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label for="filterRank">ランク帯</label>
                        <select id="filterRank" onchange="applyFilters()">
                            <option value="">すべて</option>
                            <option value="iron">Iron</option>
                            <option value="bronze">Bronze</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                            <option value="platinum">Platinum</option>
                            <option value="diamond">Diamond</option>
                            <option value="ascendant">Ascendant</option>
                            <option value="immortal">Immortal</option>
                            <option value="radiant">Radiant</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 大会一覧 -->
        <div id="tournamentsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
            <div class="card" style="grid-column: 1 / -1;">
                <div style="text-align: center; padding: 2rem;">
                    <div class="loading"></div>
                    <p style="margin-top: 1rem;">読み込み中...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- フッター -->
    <footer>
        <p>&copy; 2026 V-Link Platform. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
    <script>
        // ページロード時に大会を読み込み
        document.addEventListener('DOMContentLoaded', async () => {
            await loadAndDisplayTournaments();
        });

        // 大会を読み込んで表示
        async function loadAndDisplayTournaments() {
            const filters = {
                status: document.getElementById('filterStatus').value,
                format: document.getElementById('filterFormat').value,
                rank: document.getElementById('filterRank').value
            };

            try {
                const tournaments = await loadTournaments(filters);
                displayTournaments(tournaments);
            } catch (error) {
                showError('大会の読み込みに失敗しました');
            }
        }

        // 大会を表示
        function displayTournaments(tournaments) {
            const container = document.getElementById('tournamentsList');

            if (!tournaments || tournaments.length === 0) {
                container.innerHTML = `
                    <div class="card" style="grid-column: 1 / -1;">
                        <div style="text-align: center; padding: 2rem;">
                            <p>> 大会が見つかりません</p>
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = tournaments.map(tournament => `
                <div class="card">
                    <div class="card-header">
                        <h3>${tournament.name}</h3>
                        <div style="margin-top: 0.5rem;">
                            <span class="badge badge-${tournament.status === 'open' ? 'open' : tournament.status === 'closed' ? 'closed' : 'draft'}">
                                ${tournament.status === 'open' ? '募集中' : tournament.status === 'closed' ? '募集終了' : '終了'}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>日時:</strong> ${formatDateTime(tournament.scheduled_at)}</p>
                        <p><strong>フォーマット:</strong> ${getFormatLabel(tournament.format)}</p>
                        <p><strong>参加者:</strong> ${tournament.current_participants}/${tournament.max_participants}</p>
                        <p><strong>ランク帯:</strong> ${tournament.rank_requirement || 'すべて'}</p>
                        <p style="margin-top: 1rem; color: var(--text-secondary);">${tournament.description}</p>
                    </div>
                    <div class="card-footer">
                        <a href="tournament.php?id=${tournament.id}" class="btn" style="width: 100%;">詳細を見る</a>
                    </div>
                </div>
            `).join('');
        }

        // フォーマットラベルを取得
        function getFormatLabel(format) {
            const labels = {
                'single_elim': 'シングルエリミネーション',
                'double_elim': 'ダブルエリミネーション',
                'league': 'リーグ戦'
            };
            return labels[format] || format;
        }

        // フィルターを適用
        function applyFilters() {
            loadAndDisplayTournaments();
        }
    </script>
</body>
</html>
