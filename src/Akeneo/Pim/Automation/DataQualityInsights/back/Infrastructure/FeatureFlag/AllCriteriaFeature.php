<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Enrichment features are enabled in all editions.
 * This feature-flag is used to enable/disable the additional DQI features, coded in the Enterprise-Edition (thus its name "all criteria"), such as: (not exhaustive list)
 *   - Consistency criteria on products
 *   - Live spellcheck
 *   - Quality on attributes and options
 *   - Spellcheck dictionary by locale
 */
final class AllCriteriaFeature implements FeatureFlag
{
    public function __construct(
        private FeatureFlag $onlySerenityFeature,
        private FeatureFlags $featureFlags,
    ) {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return $this->featureFlags->isEnabled('data_quality_insights')
            && $this->onlySerenityFeature->isEnabled();
    }
}
