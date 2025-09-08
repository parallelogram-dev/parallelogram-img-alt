<?php

namespace unit;

use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;
use parallelogram\imgalt\services\PromptBuilder;
use PHPUnit\Framework\TestCase;

final class PromptBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        // Monkey-patch Plugin::getInstance() and getSettings() using reflection on static $plugin
        // Since Plugin is final, we can only set Plugin::$plugin to an instance created via reflection without running constructor.
        $ref    = new ReflectionClass(Plugin::class);
        $plugin = $ref->newInstanceWithoutConstructor();
        // Inject getSettings via dynamic property with closure isn't possible; but getSettings() is inherited from BasePlugin.
        // We can set a property 'settings' for BasePlugin to return. For tests, we'll mock Plugin::getInstance()->getSettings() by overriding static property using an anonymous class via class_alias is not feasible.
        // Alternative approach: Use Craft::$app->getView()->renderTemplate is irrelevant here. PromptBuilder calls Plugin::getInstance()->getSettings() which reads BasePlugin's internal. We'll simulate by adding a global function to return a singleton where getSettings returns desired Settings using a Proxy object.
        $settings = new Settings();
        // Store on a global for our small proxy below
        $GLOBALS['__imgalt_settings'] = $settings;
        // Overwrite Plugin::getInstance using runkit not available; instead, rely on Plugin::$plugin public static and assign an object with getSettings method.
        Plugin::$plugin = new class($settings) {
            private Settings $s;

            public function __construct($s) { $this->s = $s; }

            public function getSettings(): Settings { return $this->s; }

            public $contextResolver = null;

            public static function getInstance() { return Plugin::$plugin; }
        };
    }

    private function makeAssetWithUrl(?string $url): object
    {
        // Create a minimal double that has the methods PromptBuilder calls, matching signatures loosely.
        return new class($url) {
            public ?int     $id = 1;
            private ?string $url;

            public function __construct($url) { $this->url = $url; }

            public function getUrl($transform = null) { return $this->url; }

            public function getFs()
            {
                return new class {
                    public function read($p) { return 'bytes'; }
                };
            }

            public function getPath($filename = null) { return '/path'; }

            public function getMimeType($transform = null) { return 'image/jpeg'; }
        };
    }

    public function testBuildPromptWithUrl(): void
    {
        $asset = $this->makeAssetWithUrl('https://example.com/image.jpg');
        $p     = (new PromptBuilder())->buildPrompt($asset, []);
        $this->assertSame('gpt-4o', $p['model']);
        $this->assertIsArray($p['messages']);
        $content = $p['messages'][0]['content'];
        $this->assertSame('image_url', $content[1]['type']);
        $this->assertSame('https://example.com/image.jpg', $content[1]['image_url']['url']);
    }

    public function testBuildPromptWithUploadDataUrl(): void
    {
        // Enable upload mode
        $settings                    = Plugin::$plugin->getSettings();
        $settings->sendImageAsUpload = true;

        $asset   = $this->makeAssetWithUrl(null);
        $p       = (new PromptBuilder())->buildPrompt($asset, []);
        $content = $p['messages'][0]['content'];
        $this->assertStringStartsWith('data:image/', $content[1]['image_url']['url']);
        $this->assertStringContainsString(';base64,', $content[1]['image_url']['url']);
    }
}
