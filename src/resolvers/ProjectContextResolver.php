<?php
namespace parallelogram\imgalt\\\ImageAlt\resolvers;

use craft\elements\Asset;
use craft\elements\Entry;
use parallelogram\imgalt\\\ImageAlt\resolvers\AssetContextResolverInterface;

class ProjectContextResolver implements AssetContextResolverInterface
{
    public function getContextForAsset(Asset $asset, $project = null): array
    {
        if (!$project) {
            return [
                'projectName' => null,
                'projectDescription' => null,
            ];
        }
        
        return [
            'projectName' => $project->title,
            'projectDescription' => $project->description ?? null, // Adjust this field handle if needed
        ];
    }
}
