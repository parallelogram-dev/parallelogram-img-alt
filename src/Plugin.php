<?php

declare(strict_types=1);

namespace parallelogram\imgalt;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\Asset;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterElementActionsEvent;
use parallelogram\imgalt\elements\actions\GenerateAltTextAction;
use parallelogram\imgalt\models\Settings;
use parallelogram\imgalt\resolvers\ContextResolverManager;
use parallelogram\imgalt\resolvers\DefaultResolver;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\base\Exception;

final class Plugin extends BasePlugin
{
    public static Plugin          $plugin;
    public ContextResolverManager $contextResolver;
    public bool                   $hasCpSection  = false;
    public bool                   $hasCpSettings = true;

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Craft::setAlias('@imgalt', __DIR__);

        $resolverMap           = [];
        $this->contextResolver = new ContextResolverManager(
            $resolverMap,
            new DefaultResolver()
        );

        Event::on(
            Asset::class,
            Element::EVENT_REGISTER_ACTIONS,
            static function (RegisterElementActionsEvent $e) {
                $e->actions[] = GenerateAltTextAction::class;
            }
        );

        Event::on(
            Asset::class,
            Element::EVENT_DEFINE_SIDEBAR_HTML, // emitted by element types to customize sidebar
            static function (DefineHtmlEvent $e) {
                /** @var Asset $asset */
                $asset = $e->sender;

                $id         = 'imgalt-sidebar-' . mt_rand();
                $buttonHtml = <<<HTML
<div class="meta">
    <div class="field">
        <div class="heading"><label>Img Alt</label></div>
        <div class="input ltr">
            <button type="button" id="{$id}" class="btn">
                <span class="icon" data-icon="sparkles"></span>
                Generate ALT text
            </button>
        </div>
    </div>
</div>
HTML;

                $e->html .= $buttonHtml;

                Craft::$app->getView()->registerJsWithVars(
                    fn($buttonId, $assetId) => <<<JS
$('#'+$buttonId).on('click', async () => {
    try {
        await Craft.sendActionRequest('POST', 'imgalt/alt/generate', {data:{assetId: $assetId}});
        Craft.cp.displaySuccess(Craft.t('imgalt', 'Queued ALT generation.'));
    } catch (e) {
        const msg = e?.response?.data?.message ?? e?.message ?? 'Request failed';
        Craft.cp.displayError(msg);
    }
});
JS,
                    [$id, (int) $asset->id]
                );
            }
        );

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'parallelogram\\imgalt\\console\\controllers';
        }

    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('imgalt/settings', [
            'settings' => $this->getSettings(),
        ]);
    }
}