<?php
namespace parallelogram\imgalt\resolvers;

use craft\elements\Asset;

class DefaultResolver implements AssetContextResolverInterface
{
    public function getContextForAsset(Asset $asset, mixed $context = null): array
    {
        return [
            'projectName' => null,
            'projectDescription' => null,
        ];
    }
}
