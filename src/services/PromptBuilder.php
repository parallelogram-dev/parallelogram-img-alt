<?php
declare(strict_types=1);

namespace parallelogram\imgalt\services;

use Craft;
use craft\elements\Asset;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;

final class PromptBuilder
{
    private function s(): Settings
    {
        /** @var Settings $s */
        $s = Plugin::getInstance()->getSettings();

        return $s;
    }

    // Build the chat payload (unchanged apart from calling dataUrlForAssetUpload())
    public function buildPrompt(Asset $asset, array $context = []): array
    {
        $s = $this->s();

        $instruction = <<<PROMPT
Write one short alt text sentence for this image, suitable for accessibility and SEO. Describe the image clearly and concisely. Do not use quotes, colons or semi-colons. Limit to 10–20 words.
PROMPT;

        if ($s->sendImageAsUpload) {
            $imagePart = [
                'type'      => 'image_url',
                'image_url' => ['url' => $this->dataUrlForAssetUpload($asset)],
            ];
        } else {
            $url = $asset->getUrl([
                'width'   => $s->transformMaxSize,
                'height'  => $s->transformMaxSize,
                'mode'    => $s->transformMode,
                'quality' => $s->transformQuality,
                'format'  => $s->transformFormat ?: null,
            ]) ?? $asset->getUrl();

            if (! $url) {
                throw new \RuntimeException('No public URL for this asset. Enable “Send image as upload”.');
            }

            $imagePart = [
                'type'      => 'image_url',
                'image_url' => ['url' => $url],
            ];
        }

        return [
            'model'       => 'gpt-4o',
            'messages'    => [[
                                  'role'    => 'user',
                                  'content' => [
                                      ['type' => 'text', 'text' => $instruction],
                                      $imagePart,
                                  ],
                              ]],
            'temperature' => 0.7,
        ];
    }

    /**
     * Make a resized/encoded copy using Craft's Images service and return a data: URL.
     * Falls back to original bytes if transforms aren't requested.
     */
    private function dataUrlForAssetUpload(Asset $asset): string
    {
        $s = $this->s();

        // 1) Read original bytes from the asset's filesystem
        $bytes = $asset->getFs()->read($asset->getPath());
        if ($bytes === false || $bytes === null) {
            throw new \RuntimeException("Failed to read bytes for asset #{$asset->id}");
        }

        // 2) If you resized/re-encoded to a temp file earlier, read THAT path
        //    and set $bytes = file_get_contents($tmpPath); (then unlink)
        //    BUT do NOT pass $bytes to finfo_file(); it's not a filename.

        // 3) Decide MIME without finfo_file():
        // Prefer transform format; else try finfo_buffer; else fall back to asset mime.
        $mime = null;

        // (a) From chosen output format (recommended)
        $format = $s->transformFormat ?: '';
        if ($format !== '') {
            $mime = match (strtolower($format)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => null,
            };
        }

        // (b) Try finfo_buffer on the bytes (safe; no filename)
        if ($mime === null && function_exists('finfo_open')) {
            if ($f = finfo_open(FILEINFO_MIME_TYPE)) {
                $det = finfo_buffer($f, $bytes) ?: null;
                finfo_close($f);
                if (is_string($det) && $det !== '') {
                    $mime = $det;
                }
            }
        }

        // (c) Fall back to Craft's stored mime or a generic default
        $mime ??= $asset->getMimeType() ?: 'application/octet-stream';

        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
    }
}
