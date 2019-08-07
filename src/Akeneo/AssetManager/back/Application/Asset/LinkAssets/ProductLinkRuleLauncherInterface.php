<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\LinkAssets;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Launcher of a Product Link Rule for a given Asset Family Identifier and an Asset Code.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface ProductLinkRuleLauncherInterface
{
    /**
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     * @param AssetCode[]           $assetCodes
     */
    public function launch(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): void;
}
