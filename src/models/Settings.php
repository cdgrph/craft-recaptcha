<?php

namespace cdgrph\craftrecaptcha\models;

use craft\base\Model;
use craft\helpers\App;

class Settings extends Model
{
    public bool $enabled = false;
    public string $siteKey = '';
    public string $secretKey = '';
    public float $threshold = 0.5;

    /**
     * Get the parsed site key (resolves environment variables)
     */
    public function getSiteKey(): string
    {
        return App::parseEnv($this->siteKey);
    }

    /**
     * Get the parsed secret key (resolves environment variables)
     */
    public function getSecretKey(): string
    {
        return App::parseEnv($this->secretKey);
    }

    public function defineRules(): array
    {
        return [
            [['siteKey', 'secretKey'], 'string'],
            [['enabled'], 'boolean'],
            [['threshold'], 'number', 'min' => 0.0, 'max' => 1.0],
        ];
    }
}
