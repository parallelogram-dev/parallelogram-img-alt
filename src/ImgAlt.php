<?php
namespace parallelogram\imgalt;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\web\UrlManager;
use parallelogram\imgalt\ImageAlt\resolvers\ContextResolverManager;
use parallelogram\imgalt\ImageAlt\resolvers\DefaultResolver;
use yii\base\Event;
use parallelogram\imgalt\ImageAlt\models\Settings;

final class ImageAlt extends BasePlugin
{
    public static Plugin $plugin;

    public bool $hasCpSection = false; // set true if you have a CP nav/section
    public bool $hasCpSettings = true; // set true if you expose settings UI

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

                Craft::setAlias('@gptalttext', __DIR__);

        $config      = Craft::$app->config->getConfigFromFile('gpt');
        $resolverMap = [];

        foreach ($config['resolverMap'] ?? [] as $type => $resolverClass) {
            $resolverMap[$type] = new $resolverClass();
        }

        $this->contextResolver = new ContextResolverManager(
            $resolverMap,
            new DefaultResolver()
        );

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'parallelogram\imgalt\\\\console\\controllers';
        }

    }

    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('my-plugin/settings', [
            'settings' => $this->getSettings(),
        ]);
    }
}