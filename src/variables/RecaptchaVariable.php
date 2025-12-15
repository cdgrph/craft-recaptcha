<?php

namespace cdgrph\craftrecaptcha\variables;

use craft\helpers\Html;
use craft\helpers\Template;
use cdgrph\craftrecaptcha\Plugin;
use Twig\Markup;

class RecaptchaVariable
{
    /**
     * Get the reCAPTCHA site key (parsed for environment variables)
     */
    public function getSiteKey(): string
    {
        return Plugin::getInstance()->getSettings()->getSiteKey();
    }

    /**
     * Check if reCAPTCHA is enabled
     */
    public function getIsEnabled(): bool
    {
        $settings = Plugin::getInstance()->getSettings();
        return $settings->enabled && !empty($settings->getSiteKey());
    }

    /**
     * Output the reCAPTCHA script tag
     *
     * @param array $options Optional attributes for the script tag
     */
    public function script(array $options = []): Markup
    {
        if (!$this->getIsEnabled()) {
            return Template::raw('');
        }

        $siteKey = $this->getSiteKey();
        $url = "https://www.google.com/recaptcha/api.js?render={$siteKey}";

        $attributes = array_merge([
            'src' => $url,
            'async' => true,
            'defer' => true,
        ], $options);

        return Template::raw(Html::tag('script', '', $attributes));
    }

    /**
     * Output a hidden input field for the reCAPTCHA token
     *
     * @param string $action The action name for this form
     */
    public function input(string $action = 'submit'): Markup
    {
        if (!$this->getIsEnabled()) {
            return Template::raw('');
        }

        return Template::raw(Html::hiddenInput('g-recaptcha-response', '', [
            'id' => 'g-recaptcha-response',
            'data-recaptcha-action' => $action,
        ]));
    }

    /**
     * Output inline JavaScript to execute reCAPTCHA and populate the token
     *
     * @param string $action The action name
     * @param string $formSelector CSS selector for the form (optional)
     */
    public function execute(string $action = 'submit', string $formSelector = ''): Markup
    {
        if (!$this->getIsEnabled()) {
            return Template::raw('');
        }

        $siteKey = $this->getSiteKey();
        $escapedAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $escapedSelector = htmlspecialchars($formSelector, ENT_QUOTES, 'UTF-8');

        $script = <<<JS
<script>
grecaptcha.ready(function() {
    var form = document.querySelector('{$escapedSelector}') || document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            grecaptcha.execute('{$siteKey}', {action: '{$escapedAction}'}).then(function(token) {
                var input = form.querySelector('#g-recaptcha-response') || document.createElement('input');
                input.type = 'hidden';
                input.name = 'g-recaptcha-response';
                input.id = 'g-recaptcha-response';
                input.value = token;
                if (!form.contains(input)) {
                    form.appendChild(input);
                }
                form.submit();
            });
        });
    }
});
</script>
JS;

        return Template::raw($script);
    }
}
