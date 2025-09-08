<?php

namespace unit;

use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;
use parallelogram\imgalt\resolvers\AssetContextResolverInterface;
use parallelogram\imgalt\services\AltTextService;
use PHPUnit\Framework\TestCase;

final class AltTextServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Provide Plugin::$plugin with settings and contextResolver stub
        $settings       = new Settings();
        $resolver       = new class implements AssetContextResolverInterface {
            public function getContextForAsset($asset, mixed $context = null): array { return []; }
        };
        Plugin::$plugin = new class($settings, $resolver) {
            private $s;
            public  $contextResolver;

            public function __construct($s, $r)
            {
                $this->s               = $s;
                $this->contextResolver = $r;
            }

            public function getSettings(): Settings { return $this->s; }

            public static function getInstance(): Plugin { return Plugin::$plugin; }
        };
    }

    public function testGenerateForAssetReturnsTrimmedString(): void
    {
        $asset  = new class {
            public ?int $id = 1;
        };
        $svc    = new AltTextService();
        $result = $svc->generateForAsset($asset);
        $this->assertIsString($result);
        $this->assertNotSame('', $result);
    }
}
