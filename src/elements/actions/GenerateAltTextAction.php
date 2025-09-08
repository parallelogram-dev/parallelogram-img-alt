<?php

declare(strict_types=1);

namespace parallelogram\imgalt\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Asset;
use parallelogram\imgalt\jobs\GenerateAltTextJob;
use parallelogram\imgalt\Plugin;

final class GenerateAltTextAction extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('imgalt', 'Generate ALT text');
    }

    public function performAction(ElementQueryInterface $query): bool
    {
        /** @var Asset[] $assets */
        $assets = $query->all();

        foreach ($assets as $asset) {
            Craft::$app->getQueue()->push(new GenerateAltTextJob([
                'assetId' => (int)$asset->id,
            ]));
        }

        if (method_exists($this, 'setMessage')) {
            $this->setMessage(Craft::t('imgalt', 'Queued ALT generation for {n} asset(s).', [
                'n' => count($assets),
            ]));
        }

        return true;
    }
}
