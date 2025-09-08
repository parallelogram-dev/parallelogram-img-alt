<?php

namespace unit;

use parallelogram\imgalt\models\Settings;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $s = new Settings();
        $this->assertTrue($s->autoGenerateOnUpload);
        $this->assertFalse($s->sendImageAsUpload);
        $this->assertSame(256, $s->maxTokens);
        $this->assertSame('en-AU', $s->language);
        $this->assertSame(1024, $s->transformMaxSize);
        $this->assertSame('fit', $s->transformMode);
        $this->assertSame('jpg', $s->transformFormat);
        $this->assertSame(82, $s->transformQuality);
    }

    public function testRulesValidation(): void
    {
        $s = new Settings([
            'maxTokens'        => 0,
            'transformMaxSize' => 10,
            'transformQuality' => 101,
            'transformMode'    => 'bogus',
            'transformFormat'  => 'gif',
        ]);
        $this->assertFalse($s->validate());
        $errors = $s->getErrors();
        $this->assertArrayHasKey('maxTokens', $errors);
        $this->assertArrayHasKey('transformMaxSize', $errors);
        $this->assertArrayHasKey('transformQuality', $errors);
        $this->assertArrayHasKey('transformMode', $errors);
        $this->assertArrayHasKey('transformFormat', $errors);
    }
}
