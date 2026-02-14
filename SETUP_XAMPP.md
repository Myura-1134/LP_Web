# V-Link Platform - XAMPP セットアップガイド

V-LinkプラットフォームをXAMPPで実行するための手順です。

## 前提条件

- XAMPP がインストール済み（Apache + MySQL + PHP）
- VSCode がインストール済み

## セットアップ手順

### 1. プロジェクトをXAMPPのhtdocsにコピー

```bash
# Windowsの場合
xcopy v-link-php C:\xampp\htdocs\v-link /E /I

# Macの場合
cp -r v-link-php /Applications/XAMPP/htdocs/v-link

# Linuxの場合
cp -r v-link-php /opt/lampp/htdocs/v-link
```

### 2. XAMPPコントロールパネルを起動

- **Windows**: `C:\xampp\xampp-control.exe`
- **Mac**: `/Applications/XAMPP/XAMPP Control.app`
- **Linux**: `sudo /opt/lampp/manager-linux-x64.run`

### 3. ApacheとMySQLを起動

1. XAMPPコントロールパネルで「Apache」の「Start」をクリック
2. XAMPPコントロールパネルで「MySQL」の「Start」をクリック

### 4. データベースを作成

1. ブラウザで `http://localhost/phpmyadmin` にアクセス
2. 「新規」をクリック
3. データベース名に `v_link` を入力
4. 「作成」をクリック
5. 作成したデータベースを選択
6. 「SQL」タブをクリック
7. `sql/schema.sql` の内容をコピー＆ペースト
8. 「実行」をクリック

### 5. プロジェクト設定を確認

`config/database.php` を開き、以下の設定を確認：

```php
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';  // XAMPPのデフォルトではパスワードなし
$db_name = 'v_link';
```

### 6. ブラウザでアクセス

```
http://localhost/v-link/public/index.php
```

## トラブルシューティング

### Apache が起動しない

- ポート 80 が他のプロセスで使用されていないか確認
- ファイアウォール設定を確認

### MySQL が起動しない

- ポート 3306 が他のプロセスで使用されていないか確認
- `C:\xampp\mysql\data` フォルダを削除して再起動

### データベース接続エラー

- `config/database.php` の接続情報を確認
- phpMyAdmin で `v_link` データベースが作成されているか確認

### ページが表示されない

- Apache のエラーログを確認：`C:\xampp\apache\logs\error.log`
- PHP のエラーログを確認：`C:\xampp\php\logs\php_error.log`

## VSCode での開発

### 拡張機能をインストール

1. VSCode を起動
2. 拡張機能（Ctrl+Shift+X）を開く
3. 以下をインストール：
   - PHP Intelephense
   - PHP Debug
   - MySQL

### プロジェクトを開く

1. ファイル > フォルダーを開く
2. `C:\xampp\htdocs\v-link` を選択

### デバッグ設定

`.vscode/launch.json` を作成：

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "port": 9003,
            "pathMapping": {
                "/v-link": "${workspaceFolder}"
            }
        }
    ]
}
```

## ファイル構造

```
v-link/
├── api/                    # API エンドポイント
│   ├── tournaments.php      # 大会API
│   ├── entries.php          # エントリーAPI
│   ├── teams.php            # チームAPI
│   └── users.php            # ユーザーAPI
├── config/                 # 設定ファイル
│   ├── database.php         # データベース接続
│   └── constants.php        # 定数定義
├── includes/               # 共通ファイル
│   └── functions.php        # ユーティリティ関数
├── public/                 # 公開ファイル
│   ├── css/
│   │   └── style.css        # スタイルシート
│   ├── js/
│   │   └── main.js          # メインJavaScript
│   ├── index.php            # ホームページ
│   ├── login.php            # ログインページ
│   ├── register.php         # 登録ページ
│   ├── tournaments.php      # 大会一覧ページ
│   ├── tournament.php       # 大会詳細ページ
│   ├── create_tournament.php # 大会作成ページ
│   ├── teams.php            # チーム管理ページ
│   └── dashboard.php        # ダッシュボード
├── sql/                    # SQLファイル
│   └── schema.sql           # データベーススキーマ
└── README.md               # プロジェクト説明
```

## デフォルトアカウント

テスト用のアカウントが自動作成されます：

- **ユーザー名**: testuser
- **パスワード**: password123
- **タイプ**: participant

## セキュリティ注意事項

本番環境では以下の対策が必要です：

1. **パスワードハッシング**: `password_hash()` を使用（実装済み）
2. **SQL インジェクション対策**: プリペアドステートメントを使用（実装済み）
3. **CSRF 対策**: トークン検証を追加
4. **HTTPS**: SSL証明書を設定
5. **データベースパスワード**: 強力なパスワードを設定

## サポート

問題が発生した場合は、エラーメッセージをコピーして検索してください。

## ライセンス

MIT License
