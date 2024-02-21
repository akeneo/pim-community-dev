<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class OnlyGrowthAndSerenitySandboxFeatureFlag implements FeatureFlag
{
    public function __construct(
        private readonly bool $isSerenitySandbox,
        private readonly bool $isGrowthEditionSandbox,
    ) {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return $this->isSerenitySandbox || $this->isGrowthEditionSandbox;
    }
}
