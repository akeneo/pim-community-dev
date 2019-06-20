<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Repository;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface AssetIndexerInterface
{
    /**
     * Indexes multiple assets
     *
     * @param AssetIdentifier $assetIdentifier
     */
    public function index(AssetIdentifier $assetIdentifier);

    /**
     * Indexes all assets belonging to the given asset family.
     */
    public function indexByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): void;

    /**
     * Remove all assets belonging to an asset family
     */
    public function removeByAssetFamilyIdentifier(string $assetFamilyIdentifier);

    /**
     * Remove a asset from the index
     */
    public function removeAssetByAssetFamilyIdentifierAndCode(
        string $assetFamilyIdentifier,
        string $assetCode
    );

    public function refresh();
}
