<?php

namespace unit;

use parallelogram\imgalt\elements\actions\GenerateAltTextAction;
use parallelogram\imgalt\jobs\GenerateAltTextJob;
use PHPUnit\Framework\TestCase;

final class GenerateAltTextActionTest extends TestCase
{
    protected function setUp(): void
    {
        // reset queue and registry before each test
        \Craft::$app->queue->pushed = [];
        \AssetStub::$registry = [];
    }

    private function makeQueryReturning(array $assets): object
    {
        // Use PHPUnit stub of the real interface to avoid implementing all methods
        $stub = $this->createStub(\craft\elements\db\ElementQueryInterface::class);
        $stub->method('all')->willReturn($assets);
        return $stub;
    }

    public function testDisplayName(): void
    {
        $this->assertSame('Generate ALT text', GenerateAltTextAction::displayName());
    }

    public function testPerformActionQueuesJobsAndSetsMessage(): void
    {
        // create three assets with ids 1,2,3 in stub registry
        $assets = [];
        foreach ([1,2,3] as $id) {
            $a = new \AssetStub();
            $a->id = $id;
            \AssetStub::$registry[$id] = $a;
            $assets[] = $a;
        }

        $query  = $this->makeQueryReturning($assets);
        $action = new GenerateAltTextAction();

        $ok = $action->performAction($query);

        $this->assertTrue($ok);
        // ensure 3 jobs were queued
        $this->assertCount(3, \Craft::$app->queue->pushed);
        foreach ([1,2,3] as $idx => $id) {
            $job = \Craft::$app->queue->pushed[$idx];
            $this->assertInstanceOf(GenerateAltTextJob::class, $job);
            $this->assertSame($id, $job->assetId);
        }

        // ElementAction stub in bootstrap provides getMessage()
        $this->assertSame('Queued ALT generation for 3 asset(s).', method_exists($action, 'getMessage') ? $action->getMessage() : '');
    }

    public function testPerformActionWithNoAssets(): void
    {
        $query  = $this->makeQueryReturning([]);
        $action = new GenerateAltTextAction();

        $ok = $action->performAction($query);

        $this->assertTrue($ok);
        $this->assertCount(0, \Craft::$app->queue->pushed);
        $this->assertSame('Queued ALT generation for 0 asset(s).', method_exists($action, 'getMessage') ? $action->getMessage() : '');
    }
}
