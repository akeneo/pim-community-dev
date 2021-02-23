<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\MassEditAssets;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
interface MassEditAssetsLauncherInterface
{
    public function launchForAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $assetQuery,
        array $updaters
    ): void;
}
