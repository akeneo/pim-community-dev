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
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteAssetFamilyNamingConventionHandler
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private NamingConventionLauncherInterface $namingConventionLauncher;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        NamingConventionLauncherInterface $namingConventionLauncher
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->namingConventionLauncher = $namingConventionLauncher;
    }

    public function __invoke(ExecuteAssetFamilyNamingConventionCommand $command): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier);

        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);

        if (!$assetFamily->getNamingConvention()->isEmpty()) {
            $this->namingConventionLauncher->launchForAllAssetFamilyAssets($assetFamilyIdentifier);
        }
    }
}
