<?php

namespace unit;

use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\Plugin;
use parallelogram\imgalt\resolvers\ContextResolverManager;
use parallelogram\imgalt\resolvers\DefaultResolver;
use PHPUnit\Framework\TestCase;

final class ResolversTest extends TestCase
{
    public function testDefaultResolverReturnsShape(): void
    {
        $r = new DefaultResolver();
        $asset = new \AssetStub(); $asset->id = 5;
        $ctx = $r->getContextForAsset($asset);
        $this->assertArrayHasKey('projectName', $ctx);
        $this->assertArrayHasKey('projectDescription', $ctx);
    }

    public function testContextResolverManagerFallsBackToDefault(): void
    {
        $mgr = new ContextResolverManager([], new DefaultResolver());
        $asset = new \AssetStub(); $asset->id = 9;
        $ctx = $mgr->getContextForAsset($asset);
        $this->assertArrayHasKey('projectName', $ctx);
        $this->assertArrayHasKey('projectDescription', $ctx);
    }
}
