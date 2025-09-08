<?php
namespace parallelogram\imgalt\jobs;

use Craft;
use craft\elements\Asset;
use craft\errors\ElementNotFoundException;
use craft\queue\BaseJob;
use parallelogram\imgalt\services\AltTextService;
use yii\base\Exception;

class GenerateAltTextJob extends BaseJob
{
    public int $assetId;

    /**
     * @throws Exception
     * @throws \Throwable
     * @throws ElementNotFoundException
     */
    public function execute($queue): void
    {
        $asset = Asset::find()->id($this->assetId)->one();

        if (!$asset) {
            Craft::warning("Asset ID {$this->assetId} not found.", __METHOD__);
            return;
        }

        $service = new AltTextService();
        $caption = $service->generateForAsset($asset);

        if ($caption) {
            $asset->alt = $caption;
            Craft::$app->getElements()->saveElement($asset);
        } else {
            Craft::warning("Failed to generate alt text for asset ID {$this->assetId}", __METHOD__);
        }
    }

    protected function defaultDescription(): ?string
    {
        return "Generating alt text for asset ID {$this->assetId}";
    }
}
