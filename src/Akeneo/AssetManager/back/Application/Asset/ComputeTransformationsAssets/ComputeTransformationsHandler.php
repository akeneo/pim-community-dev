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

class ComputeTransformationsHandler
{
    /** @var ComputeTransformationLauncherInterface */
    private $computeTransformationLauncher;

    public function __construct(ComputeTransformationLauncherInterface $computeTransformationLauncher)
    {
        $this->computeTransformationLauncher = $computeTransformationLauncher;
    }

    public function handle(ComputeTransformationsCommand $command): void
    {
        $this->computeTransformationLauncher->launch(array_map(function ($assetIdenfier) {
            return AssetIdentifier::fromString($assetIdenfier);
        }, $command->getAssetIdentifiers()));
    }
}
