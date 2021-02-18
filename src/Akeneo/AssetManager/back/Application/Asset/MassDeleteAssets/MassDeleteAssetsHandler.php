<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\MassDeleteAssets;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;

/**
 * Handler to mass delete belonging to an asset family
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteAssetsHandler
{
    private MassDeleteAssetsLauncherInterface $massDeleteAssetLauncher;

    public function __construct(MassDeleteAssetsLauncherInterface $massDeleteAssetLauncher)
    {
        $this->massDeleteAssetLauncher = $massDeleteAssetLauncher;
    }

    public function __invoke(MassDeleteAssetsCommand $massDeleteAssetsCommand): void
    {
        $this->massDeleteAssetLauncher->launchForAssetFamilyAndQuery(
            AssetFamilyIdentifier::fromString($massDeleteAssetsCommand->assetFamilyIdentifier),
            AssetQuery::createFromNormalized($massDeleteAssetsCommand->query)
        );
    }
}
