<?php
namespace  parallelogram\imgalt\resolvers;

use craft\elements\Asset;

interface AssetContextResolverInterface
{
    public function getContextForAsset(Asset $asset): array;
}
