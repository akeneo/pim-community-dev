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

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetFamilyIdentifierLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Webmozart\Assert\Assert;

class ComputeTransformationsFromAssetFamilyIdentifierLauncherSpy implements ComputeTransformationFromAssetFamilyIdentifierLauncherInterface
{
    private array $launches = [];

    public function launch(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $this->launches[] = $assetFamilyIdentifier;
    }

    public function assertHasLaunches(int $expectedCount)
    {
        Assert::count($this->launches, $expectedCount);
    }
}
