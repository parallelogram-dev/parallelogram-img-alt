<?php

namespace unit;

use parallelogram\imgalt\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class PluginBasicTest extends TestCase
{
    public function testCreateSettingsModelReturnsSettings(): void
    {
        $ref = new ReflectionClass(Plugin::class);
        $plugin = $ref->newInstanceWithoutConstructor();

        $m = new ReflectionMethod(Plugin::class, 'createSettingsModel');
        $m->setAccessible(true);
        $settings = $m->invoke($plugin);
        $this->assertInstanceOf(\parallelogram\imgalt\models\Settings::class, $settings);
    }
}
