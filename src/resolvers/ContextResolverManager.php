<?php
namespace parallelogram\imgalt\resolvers;

use Craft;
use craft\db\Query;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use yii\base\InvalidConfigException;

class ContextResolverManager implements AssetContextResolverInterface
{
    private array $resolverMap;
    private AssetContextResolverInterface $defaultResolver;

    public function __construct(array $resolverMap, AssetContextResolverInterface $defaultResolver)
    {
        $this->resolverMap = $resolverMap;
        $this->defaultResolver = $defaultResolver;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getContextForAsset(Asset $asset, mixed $context = null): array
    {
        $relatedElementIds = (new Query())
            ->select(['sourceId'])
            ->from('{{%relations}}')
            ->where(['targetId' => $asset->id])
            ->column();

        foreach ($relatedElementIds as $elementId) {
            $element = Craft::$app->elements->getElementById($elementId);

            if ($element instanceof Entry) {
                return $this->resolveEntry($element, $asset);
            }

            if ($element instanceof MatrixBlock) {
                $owner = $element->getOwner();
                if ($owner instanceof Entry) {
                    return $this->resolveEntry($owner, $asset);
                }
            }
        }

        return $this->defaultResolver->getContextForAsset($asset, $context);
    }

    protected function resolveEntry(Entry $entry, Asset $asset): array
    {
        $entryTypeHandle = $entry->type->handle ?? null;

        if ($entryTypeHandle && isset($this->resolverMap[$entryTypeHandle])) {
            return $this->resolverMap[$entryTypeHandle]->getContextForAsset($asset, $entry);
        }

        return $this->defaultResolver->getContextForAsset($asset, $entry);
    }
}
