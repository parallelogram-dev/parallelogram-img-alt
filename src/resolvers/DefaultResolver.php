<?php
namespace parallelogram\imgalt\\\ImageAlt\resolvers;

use craft\elements\Asset;

class DefaultResolver implements AssetContextResolverInterface
{
    public function getContextForAsset(Asset $asset): array
    {
        return [
            'projectName' => null,
            'projectDescription' => null,
        ];
    }
}
