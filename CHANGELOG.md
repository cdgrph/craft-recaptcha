# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.2.0] - 2026-02-26

### Added
- Add `readyClass` option to `execute()` for multi-step form support
  - When specified, reCAPTCHA only executes when the form has the given CSS class
  - Other form submissions (e.g., preview/confirm steps) pass through uninterrupted
  - Backward compatible: existing usage without options works unchanged

## [1.1.0] - 2026-01-14

### Fixed
- Fix `grecaptcha is not defined` error when reCAPTCHA script loads asynchronously
- Add retry mechanism to wait for grecaptcha object before calling `grecaptcha.ready()`
- Prevent duplicate form submissions with `isExecuting` flag
- Skip reCAPTCHA execution if token already exists in form
- Add error handling for reCAPTCHA execution failures
- Use `requestSubmit()` for better form validation support with fallback to `submit()`
- Add `DOMContentLoaded` check for proper initialization timing

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
