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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

class ComputeTransformationsFromAssetFamilyIdentifierHandler
{
    private ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationLauncher;

    public function __construct(ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationLauncher)
    {
        $this->computeTransformationLauncher = $computeTransformationLauncher;
    }

    public function handle(ComputeTransformationsFromAssetFamilyIdentifierCommand $command): void
    {
        // Check there is transformation in the family before launching a job
        $this->computeTransformationLauncher->launch(AssetFamilyIdentifier::fromString($command->getAssetFamilyIdentifier()));
    }
}
