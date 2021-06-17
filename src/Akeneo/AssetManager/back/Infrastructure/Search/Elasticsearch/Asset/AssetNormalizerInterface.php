<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface AssetNormalizerInterface
{
    public function normalizeAsset(AssetIdentifier $assetIdentifier): array;

    /**
     * @param AssetIdentifier[] $assetIdentifiers
     */
    public function normalizeAssets(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetIdentifiers): array;
}
