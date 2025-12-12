<?php

namespace cdgrph\craftrecaptcha\models;

use craft\base\Model;

class Settings extends Model
{
    public bool $enabled = false;
    public string $siteKey = '';
    public string $secretKey = '';
    public float $threshold = 0.5;

    public function defineRules(): array
    {
        return [
            [['siteKey', 'secretKey'], 'string'],
            [['enabled'], 'boolean'],
            [['threshold'], 'number', 'min' => 0.0, 'max' => 1.0],
        ];
    }
}
