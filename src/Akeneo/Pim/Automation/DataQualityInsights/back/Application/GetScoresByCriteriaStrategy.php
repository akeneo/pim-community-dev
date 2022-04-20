<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetScoresByCriteriaStrategy
{
    public function __construct(
        private FeatureFlag $allCriteriaFeature,
    ) {
    }

    /**
     * Determines the scores to use, according to the current enabled features
     */
    public function __invoke(Read\Scores $scores): ChannelLocaleRateCollection
    {
        return $this->allCriteriaFeature->isEnabled() ? $scores->allCriteria() : $scores->partialCriteria();
    }
}
