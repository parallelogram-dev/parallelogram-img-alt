<?php

namespace unit;

use parallelogram\imgalt\controllers\AltController;
use parallelogram\imgalt\jobs\GenerateAltTextJob;
use PHPUnit\Framework\TestCase;

final class HttpAltControllerTest extends TestCase
{
    protected function setUp(): void
    {
        // Register an asset in the stub registry
        \AssetStub::$registry = [];
        $a = new \AssetStub();
        $a->id = 42;
        \AssetStub::$registry[$a->id] = $a;

        // Prepare request body
        \Craft::$app->request->setBody(['assetId' => $a->id]);
    }

    public function testActionGenerateQueuesJob(): void
    {
        $controller = new AltController('imgalt', null);
        $resp = $controller->actionGenerate();

        $this->assertInstanceOf(\yii\web\Response::class, $resp);
        $this->assertNotEmpty(\Craft::$app->queue->pushed);
        $job = \Craft::$app->queue->pushed[0];
        $this->assertInstanceOf(GenerateAltTextJob::class, $job);
        $this->assertSame(42, $job->assetId);
    }
}
