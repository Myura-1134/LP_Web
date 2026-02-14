<?php
/**
 * V-Link Platform - Constants Definition
 */

// アプリケーション設定
define('APP_NAME', 'V-Link');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/v-link');
define('APP_DEBUG', true);

// セッション設定
define('SESSION_NAME', 'v_link_session');
define('SESSION_TIMEOUT', 3600); // 1時間

// ユーザータイプ
define('USER_TYPE_ORGANIZER', 'organizer');
define('USER_TYPE_PARTICIPANT', 'participant');
define('USER_TYPE_BOTH', 'both');

// 大会ステータス
define('TOURNAMENT_STATUS_DRAFT', 'draft');
define('TOURNAMENT_STATUS_OPEN', 'open');
define('TOURNAMENT_STATUS_CLOSED', 'closed');
define('TOURNAMENT_STATUS_COMPLETED', 'completed');
define('TOURNAMENT_STATUS_CANCELLED', 'cancelled');

// 大会フォーマット
define('TOURNAMENT_FORMAT_SINGLE_ELIM', 'single_elim');
define('TOURNAMENT_FORMAT_DOUBLE_ELIM', 'double_elim');
define('TOURNAMENT_FORMAT_LEAGUE', 'league');

// エントリー方式
define('ENTRY_METHOD_FIRSTCOME', 'firstcome');
define('ENTRY_METHOD_LOTTERY', 'lottery');

// エントリータイプ
define('ENTRY_TYPE_INDIVIDUAL', 'individual');
define('ENTRY_TYPE_TEAM', 'team');

// エントリーステータス
define('ENTRY_STATUS_PENDING', 'pending');
define('ENTRY_STATUS_CONFIRMED', 'confirmed');
define('ENTRY_STATUS_CANCELLED', 'cancelled');
define('ENTRY_STATUS_WON', 'won');
define('ENTRY_STATUS_LOST', 'lost');

// プランタイプ
define('PLAN_TYPE_FREE', 'free');
define('PLAN_TYPE_ORGANIZER', 'organizer');
define('PLAN_TYPE_PREMIUM', 'premium');

// 価格設定
define('PLAN_ORGANIZER_PRICE', 800);  // 月額800円
define('PLAN_PREMIUM_PRICE', 500);    // 月額500円
define('TOURNAMENT_PREMIUM_PRICE', 1500); // 大会ごと1500円

// VALORANTランク
$VALORANT_RANKS = [
    'Iron',
    'Bronze',
    'Silver',
    'Gold',
    'Platinum',
    'Diamond',
    'Ascendant',
    'Immortal',
    'Radiant'
];

// 大会フォーマット表示名
$TOURNAMENT_FORMATS = [
    'single_elim' => 'シングルエリミネーション',
    'double_elim' => 'ダブルエリミネーション',
    'league' => 'リーグ戦'
];

// エントリー方式表示名
$ENTRY_METHODS = [
    'firstcome' => '先着順',
    'lottery' => '抽選'
];

// ユーザータイプ表示名
$USER_TYPES = [
    'organizer' => '主催者',
    'participant' => '参加者',
    'both' => '主催者・参加者'
];
?>
