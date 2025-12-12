# craft-recaptcha 開発ガイド・手順書

**作成日**: 2025-12-12
**対象プロジェクト**: cdgrph/craft-recaptcha
**参照**: craft-recaptcha-plugin-proposal.md

---

## 1. プロジェクト概要

Contact Form Extensions プラグインが Craft 5 で reCAPTCHA をサポートしなくなったため、独自の reCAPTCHA v3 プラグインを開発。

---

## 2. 現在の実装状況

### 完成済み (5/7 タスク)

| # | タスク | ファイル | 状態 |
|---|--------|----------|------|
| 1 | プラグイン基本構造 | `composer.json`, `src/Plugin.php`, `src/models/Settings.php` | ✅ 完了 |
| 2 | 検証サービス | `src/services/RecaptchaService.php` | ✅ 完了 |
| 3 | Twig 変数 | `src/variables/RecaptchaVariable.php` | ✅ 完了 |
| 4 | イベントフック | `src/Plugin.php` (Contact Form連携) | ✅ 完了 |
| 5 | CP設定画面 | `templates/_settings.twig` | ✅ 完了 |
| 6 | tisoffice 統合 | - | ⏳ 未実施 |
| 7 | GitHub 公開 | - | ⏳ 未実施 |

### 実装済み機能詳細

```
craft-recaptcha/
├── src/
│   ├── Plugin.php              ✅ メインプラグインクラス
│   │   - Craft Variable 登録
│   │   - Contact Form イベントフック
│   │   - 設定画面レンダリング
│   │
│   ├── models/
│   │   └── Settings.php        ✅ 設定モデル
│   │       - enabled (bool)
│   │       - siteKey (string)
│   │       - secretKey (string)
│   │       - threshold (float)
│   │
│   ├── services/
│   │   └── RecaptchaService.php ✅ 検証ロジック
│   │       - verify() - トークン検証
│   │       - sendVerifyRequest() - Google API通信
│   │
│   └── variables/
│       └── RecaptchaVariable.php ✅ Twig変数
│           - getSiteKey()
│           - getIsEnabled()
│           - script()
│           - input()
│           - execute()
│
├── templates/
│   └── _settings.twig          ✅ CP設定画面
│
├── composer.json               ✅ パッケージ定義
├── LICENSE                     ✅ MIT ライセンス
└── README.md                   ✅ 使用方法ドキュメント
```

---

## 3. 未完了・追加が必要な項目

### 3.1 必須項目

#### a) 翻訳ファイル（多言語対応）

現在 `Craft::t('recaptcha', '...')` を使用しているが、翻訳ファイルがない。

**作成が必要なファイル:**
```
src/translations/
├── en/
│   └── recaptcha.php
└── ja/
    └── recaptcha.php
```

**翻訳が必要な文字列:**
- `'reCAPTCHA verification failed. Please try again.'`
- `'Enable reCAPTCHA'`
- `'Site Key'`
- `'Secret Key'`
- `'Score Threshold'`
- その他 `_settings.twig` 内の文字列

#### b) CHANGELOG.md（変更履歴）

公開前に作成が必要。

```markdown
# Changelog

## 1.0.0 - 2025-XX-XX
### Added
- Initial release
- reCAPTCHA v3 verification
- Contact Form plugin integration
- Control Panel settings
- Twig variables: script(), input(), execute()
- Environment variable configuration support
```

#### c) .gitignore

```
/vendor/
.DS_Store
.idea/
*.log
```

#### d) .gitattributes（Composer配布最適化）

```
/.github export-ignore
/tests export-ignore
/.gitattributes export-ignore
/.gitignore export-ignore
/phpunit.xml export-ignore
```

### 3.2 推奨項目

#### a) ユニットテスト

```
tests/
├── unit/
│   ├── RecaptchaServiceTest.php    # 検証ロジックのテスト
│   └── SettingsTest.php            # 設定バリデーションテスト
├── _bootstrap.php
└── _craft/
    └── config/
```

**テスト対象:**
- Settings モデルのバリデーション
- RecaptchaService::verify() のロジック
- RecaptchaVariable の出力

#### b) コード品質ツール

- `.editorconfig` - エディタ設定統一
- `phpstan.neon` - 静的解析
- `.php-cs-fixer.php` - コードスタイル

#### c) GitHub Actions (CI/CD)

```yaml
# .github/workflows/ci.yml
name: CI
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install
      - run: composer test
```

---

## 4. テスト項目チェックリスト

### 4.1 インストール・設定テスト

- [ ] `composer require cdgrph/craft-recaptcha` でインストールできる
- [ ] `php craft plugin/install recaptcha` で有効化できる
- [ ] CP の「設定 → reCAPTCHA」に設定画面が表示される
- [ ] 各設定項目が保存・読み込みされる
  - [ ] Enable/Disable スイッチ
  - [ ] Site Key
  - [ ] Secret Key
  - [ ] Score Threshold (0.0-1.0)
- [ ] 環境変数での設定が機能する (`config/recaptcha.php`)

### 4.2 フロントエンドテスト

- [ ] `craft.recaptcha.isEnabled` が正しく動作する
- [ ] `craft.recaptcha.siteKey` がサイトキーを返す
- [ ] `craft.recaptcha.script()` が正しいスクリプトタグを出力する
- [ ] `craft.recaptcha.input('action')` が hidden input を出力する
- [ ] `craft.recaptcha.execute('action')` が実行スクリプトを出力する
- [ ] reCAPTCHA が無効時、各メソッドが空を返す

### 4.3 検証テスト

- [ ] 有効なトークンで検証成功
- [ ] 無効なトークンで検証失敗
- [ ] スコアが閾値未満で検証失敗
- [ ] Secret Key 未設定時に適切なエラー
- [ ] Google API 通信エラー時の適切なハンドリング
- [ ] action パラメータの検証（オプション）

### 4.4 Contact Form 連携テスト

- [ ] Contact Form プラグインがある場合、自動でフックされる
- [ ] Contact Form がない場合、エラーなく動作する
- [ ] reCAPTCHA 検証成功時、フォーム送信が進む
- [ ] reCAPTCHA 検証失敗時、エラーメッセージが表示される
- [ ] トークンがない場合、適切なエラー

### 4.5 エッジケーステスト

- [ ] 閾値が 0.0 の場合（常に通過）
- [ ] 閾値が 1.0 の場合（ほぼ常に失敗）
- [ ] Site Key/Secret Key が空の場合
- [ ] 環境変数とCP設定の優先順位

---

## 5. 公開前チェックリスト

### 5.1 コード品質

- [ ] すべてのファイルが PSR-4 に準拠
- [ ] 適切な PHPDoc コメントがある
- [ ] エラーハンドリングが適切
- [ ] セキュリティ上の問題がない

### 5.2 ドキュメント

- [ ] README.md が最新
- [ ] CHANGELOG.md が作成済み
- [ ] LICENSE ファイルがある
- [ ] composer.json の情報が正確

### 5.3 Git/GitHub

- [ ] .gitignore が適切
- [ ] .gitattributes が設定済み
- [ ] 不要なファイルがコミットされていない
- [ ] 意味のあるコミットメッセージ
- [ ] タグ付け（v1.0.0）

---

## 6. 統合テスト手順（tisoffice）

### Step 1: プラグインのインストール

```bash
# tisoffice プロジェクトで
cd /path/to/tisoffice

# composer.json にリポジトリ追加
# "repositories": [{"type": "vcs", "url": "git@github.com:cdgrph/craft-recaptcha.git"}]

composer require cdgrph/craft-recaptcha:dev-main
php craft plugin/install recaptcha
```

### Step 2: 環境変数の設定

```env
# .env
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=your_site_key_here
RECAPTCHA_SECRET_KEY=your_secret_key_here
RECAPTCHA_THRESHOLD=0.5
```

### Step 3: config ファイル作成

```php
// config/recaptcha.php
<?php

use craft\helpers\App;

return [
    'enabled' => App::env('RECAPTCHA_ENABLED') ?? false,
    'siteKey' => App::env('RECAPTCHA_SITE_KEY'),
    'secretKey' => App::env('RECAPTCHA_SECRET_KEY'),
    'threshold' => App::env('RECAPTCHA_THRESHOLD') ?? 0.5,
];
```

### Step 4: テンプレート修正

**Before (現在のコード):**
```twig
{% set cfePlugin = craft.app.plugins.getPlugin('contact-form-extensions') %}
{% if cfePlugin and cfePlugin.settings.recaptcha %}
<script src="https://www.google.com/recaptcha/api.js?render={{ cfePlugin.settings.recaptchaSiteKey }}"></script>
{% endif %}
```

**After (新しいコード):**
```twig
{# Head または body 終了前 #}
{{ craft.recaptcha.script() }}

{# フォーム内 #}
{{ craft.recaptcha.input('contact') }}

{# フォーム後 #}
{{ craft.recaptcha.execute('contact') }}
```

### Step 5: 動作確認

1. お問い合わせページにアクセス
2. reCAPTCHA バッジが表示されることを確認
3. フォームを送信
4. 正常に送信されることを確認
5. ログでスコアを確認

---

## 7. 今後の拡張候補

- [ ] reCAPTCHA v2 (checkbox) サポート
- [ ] 複数 action 対応のより高度な設定
- [ ] スコアのログ記録・分析機能
- [ ] カスタムエラーメッセージ設定
- [ ] テスト用の mock モード

---

## 8. 参考リソース

- [Google reCAPTCHA v3 ドキュメント](https://developers.google.com/recaptcha/docs/v3)
- [Craft CMS 5 プラグイン開発ガイド](https://craftcms.com/docs/5.x/extend/)
- [Craft CMS Plugin Store 要件](https://craftcms.com/docs/5.x/extend/plugin-store.html)
