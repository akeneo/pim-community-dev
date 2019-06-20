<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Counting the number of assets.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface CountAssetsInterface
{
    public function forAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int;
}
