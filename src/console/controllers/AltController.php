<?php

namespace parallelogram\imgalt\console\controllers;

use Craft;
use craft\console\Controller;
use craft\elements\Asset;
use Exception;
use parallelogram\imgalt\services\AltTextService;
use Throwable;
use yii\console\ExitCode;

class AltController extends Controller
{
    /**
     * @throws Throwable
     */
    public function actionGenerate(): int
    {
        $assets = Asset::find()
                       ->volume('*')
                       ->hasAlt(false)
                       ->all();

        $service = new AltTextService();

        foreach ($assets as $asset) {
            try {
                if (! in_array($asset->mimeType, ['image/gif', 'image/jpeg', 'image/png'])) {
                    continue;
                }

                $this->note("Processing asset #{$asset->id} ({$asset->filename})...");

                if ($asset->alt !== null) {
                    $this->warning("Asset #{$asset->id} has an alt tag");
                    continue;
                }

                $caption = $service->generateForAsset($asset);

                if ($caption) {
                    $asset->alt = $caption;
                    Craft::$app->getElements()->saveElement($asset);
                    $this->success("Alt text set: {$caption}");
                } else {
                    $this->warning("Failed to generate alt text for asset #{$asset->id}");
                }

                unset($asset);
            } catch (Exception $e) {
                $this->warning("Failed to generate alt text for asset: #{$e->getMessage()}");
            }
        }

        return ExitCode::OK;
    }
}
