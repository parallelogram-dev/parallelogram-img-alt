<?php
namespace parallelogram\imgalt\\\ImageAlt\console\controllers;

use craft\console\Controller;
use craft\elements\Asset;
use parallelogram\imgalt\\\ImageAlt\AltTextService;
use yii\console\ExitCode;

class AltController extends Controller
{
    public function actionGenerate(): int
    {
        $assets = Asset::find()->volume('*')->all();
        $service = new AltTextService();

        foreach ($assets as $asset) {
            $this->stdout("Processing asset #{$asset->id} ({$asset->filename})...\n");

            $caption = $service->generateForAsset($asset);

            if ($caption) {
                $asset->setFieldValue('alt', $caption);
                \Craft::$app->getElements()->saveElement($asset);
                $this->stdout("✅ Alt text set: {$caption}\n");
            } else {
                $this->stderr("⚠️ Failed to generate alt text for asset #{$asset->id}\n");
            }
        }

        return ExitCode::OK;
    }
}
