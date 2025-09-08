<?php

declare(strict_types=1);

namespace parallelogram\imgalt\models;

use craft\base\Model;

final class Settings extends Model
{
    public bool    $autoGenerateOnUpload = true;
    public bool    $sendImageAsUpload    = false;
    public ?string $openAiApiKey         = null;
    public int     $maxTokens            = 256;
    public ?string $language             = 'en-AU';
    public int     $transformMaxSize     = 1024;
    public string  $transformMode        = 'fit';
    public string  $transformFormat      = 'jpg';
    public int     $transformQuality     = 82;

    public function rules(): array
    {
        return [
            [['autoGenerateOnUpload', 'sendImageAsUpload'], 'boolean'],
            [['openAiApiKey', 'language', 'transformMode', 'transformFormat'], 'string'],
            [['maxTokens'], 'integer', 'min' => 1, 'max' => 4000],
            [['transformMaxSize'], 'integer', 'min' => 64, 'max' => 4096],
            [['transformQuality'], 'integer', 'min' => 1, 'max' => 100],
            [['transformMode'], 'in', 'range' => ['fit', 'crop', 'stretch']],
            [['transformFormat'], 'in', 'range' => ['jpg', 'png', 'webp', '']],
        ];
    }
}