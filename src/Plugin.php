<?php

namespace cdgrph\craftrecaptcha;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\contactform\events\SendEvent;
use craft\contactform\Mailer;
use craft\web\twig\variables\CraftVariable;
use cdgrph\craftrecaptcha\models\Settings;
use cdgrph\craftrecaptcha\services\RecaptchaService;
use cdgrph\craftrecaptcha\variables\RecaptchaVariable;
use yii\base\Event;

/**
 * reCAPTCHA v3 plugin for Craft CMS
 *
 * @property RecaptchaService $recaptcha
 * @property Settings $settings
 * @method Settings getSettings()
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                'recaptcha' => RecaptchaService::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->registerVariable();
        $this->registerContactFormHook();

        Craft::info('reCAPTCHA plugin loaded', __METHOD__);
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('recaptcha/_settings', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function registerVariable(): void
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $event->sender->set('recaptcha', RecaptchaVariable::class);
            }
        );
    }

    private function registerContactFormHook(): void
    {
        if (!class_exists(Mailer::class)) {
            return;
        }

        Event::on(
            Mailer::class,
            Mailer::EVENT_BEFORE_SEND,
            function (SendEvent $event) {
                if (!$this->getSettings()->enabled) {
                    return;
                }

                // Skip reCAPTCHA validation if disableRecaptcha flag is set in the message
                $message = $event->submission->message ?? [];
                if (is_array($message) && isset($message['disableRecaptcha'])) {
                    if (filter_var($message['disableRecaptcha'], FILTER_VALIDATE_BOOLEAN)) {
                        return;
                    }
                }

                $token = Craft::$app->getRequest()->getBodyParam('g-recaptcha-response');

                if (!$token) {
                    $event->isSpam = true;
                    $event->submission->addError('recaptcha', Craft::t('recaptcha', 'reCAPTCHA verification failed. Please try again.'));
                    return;
                }

                $result = $this->recaptcha->verify($token);

                if (!$result['success']) {
                    $event->isSpam = true;
                    $event->submission->addError('recaptcha', Craft::t('recaptcha', 'reCAPTCHA verification failed. Please try again.'));
                }
            }
        );
    }
}
