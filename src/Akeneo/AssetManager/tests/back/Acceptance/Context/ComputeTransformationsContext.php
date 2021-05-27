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

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Common\Fake\ComputeTransformationsFromAssetFamilyIdentifierLauncherSpy;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Behat\Behat\Context\Context;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ComputeTransformationsContext implements Context
{
    private ComputeTransformationsFromAssetFamilyIdentifierLauncherSpy $computeTransformationsLauncher;

    public function __construct(ComputeTransformationsFromAssetFamilyIdentifierLauncherSpy $computeTransformationsLauncher)
    {
        $this->computeTransformationsLauncher = $computeTransformationsLauncher;
    }

    /**
     * @Then /^a job has been launched to compute transformations$/
     */
    public function aJobHasBeenLaunchedToLinkAssetsToProducts(): void
    {
        $this->computeTransformationsLauncher->assertHasLaunches(1);
    }

    /**
     * @When /^the user computes transformations from the asset family "([^"]+)"$/
     */
    public function theUserComputesTransformationsFromTheAssetFamily(string $identifier): void
    {
        $this->computeTransformationsLauncher->launch(AssetFamilyIdentifier::fromString($identifier));
    }
}
