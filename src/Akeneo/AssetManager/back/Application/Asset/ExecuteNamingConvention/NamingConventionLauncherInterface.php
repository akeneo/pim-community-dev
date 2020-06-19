<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
interface NamingConventionLauncherInterface
{
    public function launchForAllAssetFamilyAssets(AssetFamilyIdentifier $assetFamilyIdentifier): void;
}
