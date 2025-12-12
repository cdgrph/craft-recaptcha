# reCAPTCHA for Craft CMS

Craft CMS 5.x 用の Google reCAPTCHA v3 プラグイン。

## 要件

- Craft CMS 5.0 以上
- PHP 8.2 以上

## インストール

`composer.json` にリポジトリを追加:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:cdgrph/craft-recaptcha.git"
    }
  ]
}
```

Composer でインストール:

```bash
composer require cdgrph/craft-recaptcha
```

コントロールパネルの **設定 → プラグイン** から有効化するか、CLI で実行:

```bash
php craft plugin/install recaptcha
```

## 設定

### コントロールパネル

**設定 → reCAPTCHA** から設定できます:

- **reCAPTCHA を有効化**: 検証の有効/無効を切り替え
- **サイトキー**: Google reCAPTCHA v3 のサイトキー
- **シークレットキー**: Google reCAPTCHA v3 のシークレットキー
- **スコア閾値**: 検証に必要な最低スコア（0.0〜1.0、デフォルト 0.5）

### 環境変数

`config/recaptcha.php` を作成:

```php
<?php

use craft\helpers\App;

return [
    'enabled' => App::env('RECAPTCHA_ENABLED') ?? false,
    'siteKey' => App::env('RECAPTCHA_SITE_KEY'),
    'secretKey' => App::env('RECAPTCHA_SECRET_KEY'),
    'threshold' => App::env('RECAPTCHA_THRESHOLD') ?? 0.5,
];
```

`.env` に追加:

```
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key
RECAPTCHA_THRESHOLD=0.5
```

## 使い方

### Twig 変数

#### 有効かどうかを確認

```twig
{% if craft.recaptcha.isEnabled %}
    {# reCAPTCHA が有効 #}
{% endif %}
```

#### サイトキーを取得

```twig
{{ craft.recaptcha.siteKey }}
```

#### スクリプトタグを出力

```twig
{{ craft.recaptcha.script() }}
{# 出力: <script src="https://www.google.com/recaptcha/api.js?render=YOUR_SITE_KEY" async defer></script> #}
```

#### hidden input を出力

```twig
{{ craft.recaptcha.input('contact') }}
```

#### 実行スクリプトを出力

```twig
{{ craft.recaptcha.execute('contact') }}
```

### フォームの実装例

```twig
{% extends "_layouts/base" %}

{% block content %}
<form method="post" accept-charset="UTF-8">
    {{ csrfInput() }}
    {{ actionInput('contact-form/send') }}

    <label for="email">メールアドレス</label>
    <input type="email" name="fromEmail" id="email" required>

    <label for="message">メッセージ</label>
    <textarea name="message[body]" id="message" required></textarea>

    {{ craft.recaptcha.input('contact') }}

    <button type="submit">送信</button>
</form>

{{ craft.recaptcha.script() }}
{{ craft.recaptcha.execute('contact') }}
{% endblock %}
```

### Contact Form プラグインとの連携

[Contact Form](https://plugins.craftcms.com/contact-form) プラグインと自動連携します。有効化すると、フォーム送信前に reCAPTCHA トークンを自動検証します。

上記のように reCAPTCHA のスクリプトと実行コードをテンプレートに追加するだけで動作します。

### PHP での利用

```php
use cdgrph\craftrecaptcha\Plugin as Recaptcha;

// トークンを検証
$result = Recaptcha::getInstance()->recaptcha->verify($token);

if ($result['success']) {
    // 検証成功
    $score = $result['score'];  // 0.0〜1.0
    $action = $result['action'];
} else {
    // 検証失敗
    $errors = $result['error_codes'];
}
```

## reCAPTCHA キーの取得方法

1. [reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin) にアクセス
2. **+** ボタンをクリックして新しいサイトを作成
3. **reCAPTCHA v3** を選択
4. ドメインを追加
5. **サイトキー** と **シークレットキー** をコピー

## ライセンス

MIT
