<?php

namespace unit;

use PHPUnit\Framework\TestCase;

final class TranslationsTest extends TestCase
{
    public function testTranslationKeysReturnStrings(): void
    {
        $msg1 = \Craft::t('imgalt', 'Generate ALT text');
        $this->assertSame('Generate ALT text', $msg1);

        $msg2 = \Craft::t('imgalt', 'Queued ALT generation for {n} asset(s).', ['n' => 3]);
        $this->assertSame('Queued ALT generation for 3 asset(s).', $msg2);
    }
}
