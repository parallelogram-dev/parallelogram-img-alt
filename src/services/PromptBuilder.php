<?php

namespace parallelogram\imgalt\services;

use Craft;
use craft\base\FsInterface;
use craft\elements\Asset;
use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;

class PromptBuilder
{
    /** @return Settings */
    private function settings(): Settings
    {
        /** @var Settings $s */
        $s = Plugin::getInstance()->getSettings();

        return $s;
    }

    /**
     * Build chat payload for either URL-fetch or upload-via-data-URL.
     */
    public function buildPrompt(Asset $asset, array $context = []): array
    {
        $s = $this->settings();

        // Build the instruction text
        $instruction = <<<PROMPT
Write one short alt text sentence for this image, suitable for accessibility and SEO. Describe the image clearly and concisely. Do not use quotes, colons or semi-colons. Limit to 10–20 words.
PROMPT;

        // Decide how to attach the image
        if ($s->sendImageAsUpload) {
            // Upload mode: send as data URL (base64)
            $imagePart = [
                'type'      => 'image_url',
                'image_url' => [
                    'url' => $this->buildDataUrlForAsset($asset), // <-- key line
                ],
            ];
        } else {
            // URL mode: send a publicly reachable URL
            // Prefer $asset->getUrl() (respects your FS) rather than manual concatenation
            $publicUrl = $asset->getUrl();
            if (! $publicUrl) {
                // fallback if your FS isn't public; you can inject your own host if you must
                $base      = Craft::parseEnv(getenv('DEFAULT_SITE_URL') ?: '') ?: '';
                $publicUrl = rtrim($base, '/') . '/' . ltrim($asset->path, '/');
            }
            $imagePart = [
                'type'      => 'image_url',
                'image_url' => [
                    'url' => $publicUrl,
                ],
            ];
        }

        // Your existing OpenAI chat payload shape
        return [
            'model'       => 'gpt-4o',
            'messages'    => [
                [
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $instruction],
                        $imagePart,
                    ],
                ],
            ],
            'temperature' => 0.7,
        ];
    }

    /**
     * Read the asset bytes from Craft’s filesystem and return a data URL.
     * Example result: "data:image/jpeg;base64,AAAA..."
     */
    private function buildDataUrlForAsset(Asset $asset): string
    {
        $fs    = $asset->getFs();
        $bytes = $fs->read($asset->getPath());   // string|false

        if ($bytes === false || $bytes === null) {
            throw new \RuntimeException("FS read failed for asset #{$asset->id}");
        }

        $mime = $asset->getMimeType() ?: 'application/octet-stream';

        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
    }

    private function guessMime(string $bytes): ?string
    {
        if (! function_exists('finfo_open')) {
            return null;
        }
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if (! $f) {
            return null;
        }
        $mime = finfo_buffer($f, $bytes) ?: null;
        finfo_close($f);

        return $mime;
    }
}
