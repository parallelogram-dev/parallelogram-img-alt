<?php
namespace  parallelogram\imgalt\\\ImageAlt\resolvers;

use craft\elements\Asset;

interface AssetContextResolverInterface
{
    public function getContextForAsset(Asset $asset): array;
}
