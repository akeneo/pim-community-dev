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
class MassEditAssetsHandler
{
    private MassEditAssetsLauncherInterface $massEditAssetLauncher;

    public function __construct(MassEditAssetsLauncherInterface $massEditAssetLauncher)
    {
        $this->massEditAssetLauncher = $massEditAssetLauncher;
    }

    public function __invoke(MassEditAssetsCommand $massEditAssetsCommand): void
    {
        $this->massEditAssetLauncher->launchForAssetFamily(
            AssetFamilyIdentifier::fromString($massEditAssetsCommand->assetFamilyIdentifier),
            AssetQuery::createFromNormalized($massEditAssetsCommand->query),
            $massEditAssetsCommand->editValueCommands
        );
    }
}
