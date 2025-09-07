<?php

namespace parallelogram\imgalt\\\ImageAlt\models;

use craft\base\Model;

final class Settings extends Model
{
    public ?string $apiKey = null;
    public int $limit = 20;

    public function rules(): array
    {
        return [
            [['apiKey'], 'string'],
            [['limit'], 'integer', 'min' => 1],
        ];
    }
}
