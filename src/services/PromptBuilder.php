<?php
namespace gptalttext\services;

use Craft;
use craft\elements\Asset;
use yii\BaseYii;
use craft\helpers\UrlHelper;

class PromptBuilder
{
    public function buildPrompt(Asset $asset, array $context = []): array
    {
        $projectName = $context['projectName'] ?? null;
        $projectDescription = $context['projectDescription'] ?? null;
        
        $path = getenv('DEFAULT_SITE_URL') . $asset->getVolume()->rootUrl . $asset->path;

        if ($projectName && $projectDescription) {
            $context = <<<PROMPT
This image is part of a project called "{$projectName}". Description: {$projectDescription}. Write one short alt text sentence suitable for accessibility and SEO. Include the project name if appropriate but do not include the word 'project'. Do not use quotes, colons or semi-colons. Limit to 10–20 words.
PROMPT;
        } elseif ($projectName) {
            $context = <<<PROMPT
This image is part of a project called "{$projectName}". Write one short alt text sentence suitable for accessibility and SEO. Include the project name if appropriate but do not include the word 'project'. Do not use quotes, colons or semi-colons. Limit to 10–20 words.
PROMPT;
        } else {
            $context = <<<PROMPT
Write one short alt text sentence for this image, suitable for accessibility and SEO. Describe the image clearly and concisely. Do not use quotes, colons or semi-colons. Limit to 10–20 words.
PROMPT;
        }

        return [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $context,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $path,
                            ],
                        ]
                    ]
                ]
            ],
            'temperature' => 0.7,
        ];
    }
}
