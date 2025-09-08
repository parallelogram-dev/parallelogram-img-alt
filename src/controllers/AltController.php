<?php

namespace parallelogram\imgalt\controllers;

namespace parallelogram\imgalt\controllers;

use Craft;
use craft\web\Controller;
use craft\elements\Asset;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class AltController extends Controller
{
    /** Only CP users; omit/adjust if you want site access too */
    protected array|int|bool $allowAnonymous = false;

    public function actionGenerate(): Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $assetId = (int)Craft::$app->getRequest()->getRequiredBodyParam('assetId');
        $asset = Asset::find()->id($assetId)->one();
        if (!$asset) {
            throw new BadRequestHttpException("Asset not found.");
        }

        // Queue your job (or call a service)
        Craft::$app->getQueue()->push(new \parallelogram\imgalt\jobs\GenerateAltTextJob([
            'assetId' => $assetId,
        ]));

        return $this->asSuccess(Craft::t('imgalt', 'Queued ALT generation.'));
    }
}
