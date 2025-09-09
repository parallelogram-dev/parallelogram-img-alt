<?php

namespace unit;

use parallelogram\imgalt\jobs\GenerateAltTextJob;
use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;
use parallelogram\imgalt\resolvers\AssetContextResolverInterface;
use PHPUnit\Framework\TestCase;

final class GenerateAltTextJobTest extends TestCase
{
    protected function setUp(): void
    {
        \AssetStub::$registry = [];
        $a = new \AssetStub(); $a->id = 7; $a->filename = 'x.jpg'; $a->mimeType = 'image/jpeg'; $a->alt = null; \AssetStub::$registry[$a->id] = $a;

        // Provide resolver for AltTextService
        $resolver = new class implements AssetContextResolverInterface { public function getContextForAsset($asset, mixed $context = null): array { return []; } };
        Plugin::$plugin = new class($resolver) {
            public $contextResolver; private $s; public function __construct($r){ $this->contextResolver=$r; $this->s=new Settings(); } public function getSettings(){ return $this->s; }
        };
    }

    public function testExecuteSavesGeneratedAlt(): void
    {
        $job = new GenerateAltTextJob(['assetId' => 7]);
        $job->execute(null);

        $this->assertCount(1, \Craft::$app->elements->saved);
        $saved = \Craft::$app->elements->saved[0];
        $this->assertSame(7, $saved->id);
        $this->assertIsString($saved->alt);
        $this->assertNotSame('', $saved->alt);
    }
}
