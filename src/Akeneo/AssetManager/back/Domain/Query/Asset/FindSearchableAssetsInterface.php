<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Query function that finds SearchAssetItem read models.
 */
interface FindSearchableAssetsInterface
{
    public function byAssetIdentifier(AssetIdentifier $assetIdentifier): ?SearchableAssetItem;

    public function byAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator;
}
