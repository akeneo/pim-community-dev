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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetIdentifiersLauncherInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ComputeTransformationFromAssetIdentifiersLauncherSpy implements ComputeTransformationFromAssetIdentifiersLauncherInterface
{
    private array $assetIdentifiersInJobs = [];

    /**
     * {@inheritDoc}
     */
    public function launch(array $assetIdentifiers): void
    {
        $this->assetIdentifiersInJobs += $assetIdentifiers;
    }

    public function assertAJobIsLaunchedWithAssetIdentifier(AssetIdentifier $assetIdentifier): void
    {
        foreach ($this->assetIdentifiersInJobs as $assetIdentifiersInJob) {
            if ($assetIdentifier->equals($assetIdentifiersInJob)) {
                return;
            }
        }

        throw new \LogicException(sprintf(
            "No compute transformation job found for asset identifier '%s'.",
            $assetIdentifier->__toString()
        ));
    }
}
