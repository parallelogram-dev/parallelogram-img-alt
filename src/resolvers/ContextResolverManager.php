<?php
namespace parallelogram\imgalt\\\ImageAlt\resolvers;

use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use craft\db\Query;

class ContextResolverManager implements AssetContextResolverInterface
{
    private array $resolverMap;
    private AssetContextResolverInterface $defaultResolver;

    public function __construct(array $resolverMap, AssetContextResolverInterface $defaultResolver)
    {
        $this->resolverMap = $resolverMap;
        $this->defaultResolver = $defaultResolver;
    }

    public function getContextForAsset(Asset $asset): array
    {
    	$relatedElementIds = (new Query())
			->select(['sourceId'])
			->from('{{%relations}}')
			->where(['targetId' => $asset->id])
			->column();
        
		foreach ($relatedElementIds as $elementId) {
        	$element = \Craft::$app->elements->getElementById($elementId);
        
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
    
        return $this->defaultResolver->getContextForAsset($asset);
    }
    
    protected function resolveEntry(Entry $entry, $asset)
    {
		$entryTypeHandle = $entry->type->handle ?? null;
		
		if ($entryTypeHandle && isset($this->resolverMap[$entryTypeHandle])) {
			return $this->resolverMap[$entryTypeHandle]->getContextForAsset($asset, $entry);
		}

        return $this->defaultResolver->getContextForAsset($asset);
    }
}
