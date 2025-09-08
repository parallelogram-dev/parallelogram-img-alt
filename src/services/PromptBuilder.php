<?php
declare(strict_types=1);

namespace parallelogram\imgalt\services;

use craft\elements\Asset;
use craft\errors\ImageTransformException;
use Craft;
use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;
use RuntimeException;
use yii\base\InvalidConfigException;

final class PromptBuilder
{
    private function s(): Settings
    {
        /** @var Settings $s */
        $s = Plugin::getInstance()->getSettings();

        return $s;
    }

    public function buildPrompt(Asset $asset, array $context = []): array
    {
        $s = $this->s();

        $instruction = Craft::t('imgalt', 'Write one short alt text sentence for this image, suitable for accessibility and SEO. Describe the image clearly and concisely. Do not use quotes, colons or semi-colons. Limit to 10–20 words.');

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
                throw new RuntimeException(Craft::t('imgalt', 'No public URL for this asset. Enable “Send image as upload”.'));
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
                    [
                        'type' => 'text',
                        'text' => $instruction
                    ],
                    $imagePart,
                ],
            ]],
            'temperature' => 0.7,
        ];
    }

    /**
     * Make a resized/encoded copy using Craft's Images service and return a data: URL.
     * Falls back to original bytes if transforms aren't requested.
     *
     * @throws InvalidConfigException
     * @throws ImageTransformException
     */
    private function dataUrlForAssetUpload(Asset $asset): string
    {
        $s = $this->s();

        $bytes = $asset->getFs()->read($asset->getPath());
        if (! $bytes) {
            throw new RuntimeException(Craft::t('imgalt', 'Failed to read bytes for asset #{id}', ['id' => $asset->id]));
        }

        $mime = null;

        $format = $s->transformFormat ?: '';
        if ($format !== '') {
            $mime = match (strtolower($format)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => null,
            };
        }

        if ($mime === null && function_exists('finfo_open')) {
            if ($f = finfo_open(FILEINFO_MIME_TYPE)) {
                $det = finfo_buffer($f, $bytes) ?: null;
                finfo_close($f);
                if (is_string($det) && $det !== '') {
                    $mime = $det;
                }
            }
        }

        $mime ??= $asset->getMimeType() ?: 'application/octet-stream';

        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
    }
}
