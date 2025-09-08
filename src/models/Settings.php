<?php

declare(strict_types=1);

namespace parallelogram\imgalt\models;

use craft\base\Model;

final class Settings extends Model
{
    public bool $autoGenerateOnUpload = true;
    public bool $sendImageAsUpload = false;
    public ?string $openAiApiKey = null;
    public int $maxTokens = 256;
    public ?string $language = 'en-AU';

    public function rules(): array
    {
        return [
            [['autoGenerateOnUpload'], 'boolean'],
            [['sendImageAsUpload'], 'boolean'],
            [['openAiApiKey', 'language'], 'string'],
            [['maxTokens'], 'integer', 'min' => 1, 'max' => 4000],
        ];
    }
}
