<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetScoresPropertyStrategy
{
    public function __construct(
        private FeatureFlag $allCriteriaFeature
    ) {
    }

    public function __invoke(): string
    {
        return $this->allCriteriaFeature->isEnabled() ? 'scores' : 'scores_partial_criteria';
    }
}
