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

    /** @TODO pull up remove this function in master */
    public function byAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator;

    /**
     * @TODO pull up add this function in master
     * @param AssetIdentifier[] $assetIdentifiers
     * @return \Iterator
     */
    //public function byAssetIdentifiers(array $assetIdentifiers): \Iterator;
}
