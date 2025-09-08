<?php
namespace parallelogram\imgalt\services;

use Craft;
use craft\elements\Asset;
use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;
use Throwable;
use yii\base\InvalidConfigException;

class AltTextService
{
    private function settings(): Settings
    {
        /** @var Settings $s */
        $s = Plugin::getInstance()->getSettings();
        return $s;
    }

    /**
     * @throws InvalidConfigException
     */
    public function generateForAsset(Asset $asset): ?string
    {
        $contextResolver = Plugin::getInstance()->contextResolver ?? null;
        $settings = Plugin::getInstance()->getSettings();

        if (!$contextResolver) {
            Craft::error("Context resolver not available", __METHOD__);
            return null;
        }

        $context = $contextResolver->getContextForAsset($asset);
        $prompt = (new PromptBuilder())->buildPrompt($asset, $context);

        try {
            $client = Craft::createGuzzleClient([
                'headers' => [
                    'Authorization' => 'Bearer ' . ($settings->openAiApiKey ?? ''),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'json' => $prompt
            ]);

            $body = (string) $response->getBody();
            unset($response); // Important for memory cleanup

            $data = json_decode($body, true);
            
            return trim($data['choices'][0]['message']['content'] ?? '');
        } catch (Throwable $e) {
            Craft::error("OpenAI error: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}
