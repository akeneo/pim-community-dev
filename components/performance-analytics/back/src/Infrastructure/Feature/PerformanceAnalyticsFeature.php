<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Infrastructure\Feature;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * To remove when Performance-Analytics will be enabled on serenity edition by default
 * Then we should not need to rely on an additional env var, but use directly the service OnlySerenityFeatureFlag.
 */
final class PerformanceAnalyticsFeature implements FeatureFlag
{
    public function __construct(
        private FeatureFlag $onlySerenityFeature,
        private mixed $featureEnabledEnvVar
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->onlySerenityFeature->isEnabled() && (bool) $this->featureEnabledEnvVar;
    }
}
