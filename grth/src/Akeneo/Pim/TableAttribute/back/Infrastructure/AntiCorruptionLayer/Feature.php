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

namespace Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

class Feature
{
    const REFERENCE_ENTITY = 'reference_entity';
    const MEASUREMENT = 'measurement';

    public function __construct(private FeatureFlags $featureFlags)
    {
    }

    public function isEnabled(string $name): bool
    {
        try {
            return $this->featureFlags->isEnabled($name);
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
