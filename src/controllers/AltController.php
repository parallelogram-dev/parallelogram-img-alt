<?php
declare(strict_types=1);

namespace parallelogram\imgalt\controllers;

use Craft;
use craft\web\Controller;
use craft\elements\Asset;
use parallelogram\imgalt\jobs\GenerateAltTextJob;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class AltController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionGenerate(): Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $assetId = (int) Craft::$app->getRequest()->getRequiredBodyParam('assetId');
        $asset   = Asset::find()->id($assetId)->one();
        if (! $asset) {
            throw new BadRequestHttpException("Asset not found.");
        }

        Craft::$app->getQueue()->push(new GenerateAltTextJob([
            'assetId' => $assetId,
        ]));

        return $this->asSuccess(Craft::t('imgalt', 'Queued ALT generation.'));
    }
}
