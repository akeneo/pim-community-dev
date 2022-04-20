<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AllCriteriaFeature implements FeatureFlag
{
    public function __construct(
        private FeatureFlags $featureFlags
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->featureFlags->isEnabled('data_quality_insights_all_criteria');
    }
}
