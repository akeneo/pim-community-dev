<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;

class InMemoryCountAssets implements CountAssetsInterface
{
    public function forAssetFamily(AssetFamilyIdentifier $identifierToMatch): int
    {
        return 3;
    }
}
