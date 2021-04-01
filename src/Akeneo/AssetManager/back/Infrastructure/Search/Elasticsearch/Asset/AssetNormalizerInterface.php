<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface AssetNormalizerInterface
{
    public function normalizeAsset(AssetIdentifier $assetIdentifier): array;

    /** @TODO pull up remove this function in master */
    public function normalizeAssetsByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator;

    /**
     * @TODO pull up add this function in master
     * @param AssetIdentifier[] $assetIdentifiers
     */
    //public function normalizeAssets(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetIdentifiers): array;
}
