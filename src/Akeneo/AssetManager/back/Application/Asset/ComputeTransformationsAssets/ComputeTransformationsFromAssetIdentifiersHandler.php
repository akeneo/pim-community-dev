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

namespace Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;

class ComputeTransformationsFromAssetIdentifiersHandler
{
    private ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher;

    public function __construct(ComputeTransformationFromAssetIdentifiersLauncherInterface $computeTransformationLauncher)
    {
        $this->computeTransformationLauncher = $computeTransformationLauncher;
    }

    public function handle(ComputeTransformationsFromAssetIdentifiersCommand $command): void
    {
        // Check there is transformation in the family before launching a job
        $this->computeTransformationLauncher->launch(array_map(fn ($assetIdenfier) => AssetIdentifier::fromString($assetIdenfier), $command->getAssetIdentifiers()));
    }
}
