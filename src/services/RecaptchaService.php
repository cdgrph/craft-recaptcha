<?php

namespace cdgrph\craftrecaptcha\services;

use Craft;
use craft\base\Component;
use cdgrph\craftrecaptcha\Plugin;

class RecaptchaService extends Component
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Verify a reCAPTCHA token
     *
     * @param string $token The reCAPTCHA response token
     * @param string|null $action Optional action name to verify
     * @return array{success: bool, score: float|null, action: string|null, error_codes: array}
     */
    public function verify(string $token, ?string $action = null): array
    {
        $settings = Plugin::getInstance()->getSettings();

        if (!$settings->enabled) {
            return [
                'success' => true,
                'score' => null,
                'action' => null,
                'error_codes' => [],
            ];
        }

        $secretKey = $settings->getSecretKey();
        if (empty($secretKey)) {
            Craft::warning('reCAPTCHA secret key is not configured', __METHOD__);
            return [
                'success' => false,
                'score' => null,
                'action' => null,
                'error_codes' => ['missing-secret-key'],
            ];
        }

        $response = $this->sendVerifyRequest($token, $secretKey);

        if ($response === null) {
            return [
                'success' => false,
                'score' => null,
                'action' => null,
                'error_codes' => ['connection-failed'],
            ];
        }

        $success = $response['success'] ?? false;
        $score = $response['score'] ?? 0.0;
        $responseAction = $response['action'] ?? null;
        $errorCodes = $response['error-codes'] ?? [];

        // Check score threshold
        if ($success && $score < $settings->threshold) {
            $success = false;
            $errorCodes[] = 'score-below-threshold';
            Craft::info("reCAPTCHA score {$score} is below threshold {$settings->threshold}", __METHOD__);
        }

        // Optionally verify action matches
        if ($success && $action !== null && $responseAction !== $action) {
            $success = false;
            $errorCodes[] = 'action-mismatch';
            Craft::warning("reCAPTCHA action mismatch: expected {$action}, got {$responseAction}", __METHOD__);
        }

        if (!$success) {
            Craft::info('reCAPTCHA verification failed: ' . implode(', ', $errorCodes), __METHOD__);
        }

        return [
            'success' => $success,
            'score' => $score,
            'action' => $responseAction,
            'error_codes' => $errorCodes,
        ];
    }

    /**
     * Send verification request to Google
     */
    private function sendVerifyRequest(string $token, string $secretKey): ?array
    {
        $data = [
            'secret' => $secretKey,
            'response' => $token,
        ];

        $remoteIp = Craft::$app->getRequest()->getUserIP();
        if ($remoteIp) {
            $data['remoteip'] = $remoteIp;
        }

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10,
            ],
        ];

        $context = stream_context_create($options);

        try {
            $response = file_get_contents(self::VERIFY_URL, false, $context);

            if ($response === false) {
                Craft::error('Failed to connect to reCAPTCHA verification server', __METHOD__);
                return null;
            }

            $result = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Craft::error('Failed to parse reCAPTCHA response: ' . json_last_error_msg(), __METHOD__);
                return null;
            }

            return $result;
        } catch (\Exception $e) {
            Craft::error('reCAPTCHA verification error: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}
