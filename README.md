# V-Link Platform - PHP版（XAMPP対応）

XAMPP環境で実行するPHPベースのVALORANT大会マッチングプラットフォーム

## プロジェクト構成

```
v-link-php/
├── config/
│   ├── database.php          # データベース接続設定
│   └── constants.php         # 定数定義
├── api/
│   ├── tournaments.php       # 大会API
│   ├── entries.php           # エントリーAPI
│   ├── teams.php             # チームAPI
│   ├── users.php             # ユーザーAPI
│   └── auth.php              # 認証API
├── includes/
│   ├── header.php            # ヘッダーテンプレート
│   ├── footer.php            # フッターテンプレート
│   └── functions.php         # ユーティリティ関数
├── public/
│   ├── index.php             # ホームページ
│   ├── tournaments.php       # 大会一覧ページ
│   ├── tournament_detail.php # 大会詳細ページ
│   ├── create_tournament.php # 大会作成ページ
│   ├── teams.php             # チーム管理ページ
│   ├── dashboard.php         # ダッシュボード
│   ├── css/
│   │   └── style.css         # レトロフューチャースタイル
│   └── js/
│       └── main.js           # メインJavaScript
├── sql/
│   └── schema.sql            # データベーススキーマ
└── .htaccess                 # Apache設定
```

## セットアップ手順

### 1. XAMPPのインストール
- https://www.apachefriends.org/ からダウンロード
- Apache、MySQL、PHPを有効化

### 2. プロジェクトの配置
```bash
# XAMPPのhtdocsディレクトリに配置
cp -r v-link-php /path/to/xampp/htdocs/v-link
```

### 3. データベースの作成
```bash
# MySQLにログイン
mysql -u root

# スキーマを実行
source /path/to/v-link-php/sql/schema.sql;
```

### 4. 設定ファイルの編集
- `config/database.php` でデータベース接続情報を設定

### 5. ブラウザでアクセス
```
http://localhost/v-link
```

## 機能

- ✅ ユーザー認証（セッションベース）
- ✅ 大会一覧・検索
- ✅ 大会作成（主催者向け）
- ✅ 大会詳細表示
- ✅ エントリー機能（個人・チーム）
- ✅ チーム管理
- ✅ 主催者ダッシュボード
- ✅ 参加者マイページ
- ✅ Discord連携（予定）
- ✅ 支払い管理（Stripe、予定）

## デザイン

レトロフューチャーな「システム障害」美学：
- 深い黒背景（oklch(0.05 0 0)）
- ネオンシアン（#00ffff）とマゼンタ（#ff00ff）の色収差エフェクト
- スキャンラインテクスチャ
- 太字サンセリフ＆等幅フォント
- 幾何学的なブラケット表示

## 技術スタック

- **言語：** PHP 7.4+
- **データベース：** MySQL 5.7+
- **フロントエンド：** HTML5, CSS3, Vanilla JavaScript
- **Webサーバー：** Apache（XAMPP付属）
