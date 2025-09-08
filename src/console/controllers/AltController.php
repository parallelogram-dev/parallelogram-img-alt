<?php
namespace parallelogram\imgalt\console\controllers;

use craft\console\Controller;
use craft\elements\Asset;
use parallelogram\imgalt\services\AltTextService;
use yii\console\ExitCode;

class AltController extends Controller
{
    public function actionGenerate(): int
    {
        $assets = Asset::find()
            ->volume('*')
            ->hasAlt(false)
            ->all();

        $service = new AltTextService();

        foreach ($assets as $asset) {
            try {
                if (! in_array($asset->mimeType, ['image/jpeg', 'image/png'])) {
                    continue;
                }

                $this->stdout("Processing asset #{$asset->id} ({$asset->filename})...\n");

                if ($asset->alt !== null) {
                    $this->stderr("⚠️ Asset #{$asset->id} has an alt tag\n");
                    continue;
                }

                $caption = $service->generateForAsset($asset);

                if ($caption) {
                    $asset->alt = $caption;
                    \Craft::$app->getElements()->saveElement($asset);
                    $this->stdout("✅ Alt text set: {$caption}\n");
                } else {
                    $this->stderr("⚠️ Failed to generate alt text for asset #{$asset->id}\n");
                }

                unset($asset);
            } catch (\Exception $e) {
                $this->stderr("⚠️ Failed to generate alt text for asset: #{$e->getMessage()}\n");
            }
        }

        return ExitCode::OK;
    }
}
