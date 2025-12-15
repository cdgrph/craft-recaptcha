# reCAPTCHA for Craft CMS

Google reCAPTCHA v3 plugin for Craft CMS 5.x with automatic Contact Form integration.

## Features

- **reCAPTCHA v3 Integration** - Invisible spam protection without user interaction
- **Contact Form Support** - Automatic validation with [Contact Form](https://plugins.craftcms.com/contact-form) plugin
- **Configurable Threshold** - Adjust score sensitivity (0.0-1.0)
- **Environment Variables** - Production-ready configuration via `.env`
- **Twig Helpers** - Easy template integration with built-in variables

## Requirements

- Craft CMS 5.0 or later
- PHP 8.2 or later

## Installation

```bash
composer require cdgrph/craft-recaptcha
```

Then install the plugin from the Control Panel (**Settings → Plugins**) or via CLI:

```bash
php craft plugin/install recaptcha
```

## Configuration

### Control Panel

Navigate to **Settings → reCAPTCHA** to configure:

| Setting | Description |
|---------|-------------|
| Enable reCAPTCHA | Toggle verification on/off |
| Site Key | Your reCAPTCHA v3 site key |
| Secret Key | Your reCAPTCHA v3 secret key |
| Score Threshold | Minimum score required (default: 0.5) |

### Environment Variables (Recommended)

Create `config/recaptcha.php`:

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

Add to `.env`:

```
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key
RECAPTCHA_THRESHOLD=0.5
```

## Usage

### Twig Variables

```twig
{# Check if enabled #}
{% if craft.recaptcha.isEnabled %}
    {# reCAPTCHA is active #}
{% endif %}

{# Get site key #}
{{ craft.recaptcha.siteKey }}

{# Output script tag #}
{{ craft.recaptcha.script() }}

{# Output hidden input #}
{{ craft.recaptcha.input('contact') }}

{# Output execution script #}
{{ craft.recaptcha.execute('contact') }}
```

### Form Example

```twig
<form method="post">
    {{ csrfInput() }}
    {{ actionInput('contact-form/send') }}

    <label for="email">Email</label>
    <input type="email" name="fromEmail" id="email" required>

    <label for="message">Message</label>
    <textarea name="message[body]" id="message" required></textarea>

    {{ craft.recaptcha.input('contact') }}

    <button type="submit">Send</button>
</form>

{{ craft.recaptcha.script() }}
{{ craft.recaptcha.execute('contact') }}
```

### Contact Form Integration

This plugin automatically integrates with the [Contact Form](https://plugins.craftcms.com/contact-form) plugin. Simply add the reCAPTCHA script and execution code to your template - validation happens automatically on form submission.

### PHP Usage

```php
use cdgrph\craftrecaptcha\Plugin as Recaptcha;

$result = Recaptcha::getInstance()->recaptcha->verify($token);

if ($result['success']) {
    $score = $result['score'];   // 0.0-1.0
    $action = $result['action'];
} else {
    $errors = $result['error_codes'];
}
```

## Getting reCAPTCHA Keys

1. Visit [reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Click **+** to create a new site
3. Select **reCAPTCHA v3**
4. Add your domain(s)
5. Copy the **Site Key** and **Secret Key**

## License

MIT
