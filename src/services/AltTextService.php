<?php
namespace parallelogram\imgalt\services;

use Craft;
use craft\elements\Asset;
use parallelogram\imgalt\services\PromptBuilder;
use parallelogram\imgalt\Plugin;

class AltTextService
{
    private function settings(): Settings
    {
        /** @var Settings $s */
        $s = Plugin::getInstance()->getSettings();
        return $s;
    }


    public function generateForAsset(Asset $asset): ?string
    {
        $contextResolver = Plugin::getInstance()->contextResolver ?? null;
        $settings = Plugin::$plugin->getSettings();

        if (!$contextResolver) {
            Craft::error("Context resolver not available", __METHOD__);
            return null;
        }

        $context = $contextResolver->getContextForAsset($asset);
        $prompt = (new PromptBuilder())->buildPrompt($asset, $context);

        try {
            $client = Craft::createGuzzleClient([
                'headers' => [
                    'Authorization' => 'Bearer ' . $settings->openAiApiKey,
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
        } catch (\Throwable $e) {
            echo "OpenAI error: " . $e->getMessage();
            Craft::error("OpenAI error: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}
