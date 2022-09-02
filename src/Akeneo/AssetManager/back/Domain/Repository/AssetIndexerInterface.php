<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Repository;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface AssetIndexerInterface
{
    /**
     * Indexes one asset
     */
    public function index(AssetIdentifier $assetIdentifier);

    /**
     * Indexes multiple assets
     *
     * @param AssetIdentifier[] $assetIdentifiers
     */
    public function indexByAssetIdentifiers(array $assetIdentifiers);

    /**
     * Indexes all assets belonging to the given asset family.
     */
    public function indexByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): void;

    /**
     * Remove a asset from the index
     */
    public function removeAssetByAssetFamilyIdentifierAndCode(
        string $assetFamilyIdentifier,
        string $assetCode
    );

    public function removeByAssetFamilyIdentifierAndCodes(string $assetFamilyIdentifier, array $assetCodes);

    public function refresh();
}
