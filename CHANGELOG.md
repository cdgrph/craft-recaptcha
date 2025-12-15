# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2025-12-15

### Fixed
- Fix Contact Form 3.x compatibility by using `isSpam` property instead of `isValid`
- Add `App::parseEnv()` support for environment variables in CP settings
- Fix settings template path for Craft CMS plugin compatibility

### Added
- Initial release
- reCAPTCHA v3 token verification with Google API
- Automatic integration with Contact Form plugin
- Control Panel settings UI
  - Enable/disable toggle
  - Site Key configuration
  - Secret Key configuration
  - Score threshold setting (0.0-1.0)
- Twig template variables
  - `craft.recaptcha.siteKey` - Get the site key
  - `craft.recaptcha.isEnabled` - Check if enabled
  - `craft.recaptcha.script()` - Output script tag
  - `craft.recaptcha.input()` - Output hidden input field
  - `craft.recaptcha.execute()` - Output execution script
- Environment variable configuration support via `config/recaptcha.php`
- PHP service for manual token verification
