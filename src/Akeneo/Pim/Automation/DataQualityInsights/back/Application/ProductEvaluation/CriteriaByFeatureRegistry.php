<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaByFeatureRegistry
{
    /**
     * @params array<CriterionCode>
     */
    private array $allCriteriaCodes = [];

    /**
     * @params array<CriterionCode>
     */
    private array $partialCriteriaCodes = [];

    public function __construct(
        private FeatureFlag $allCriteriaFeature,
    ) {
    }

    public function register(EvaluateCriterionInterface $criterionEvaluationService, ?string $feature): void
    {
        $this->allCriteriaCodes[] = $criterionEvaluationService->getCode();

        if ('data_quality_insights_all_criteria' !== $feature) {
            $this->partialCriteriaCodes[] = $criterionEvaluationService->getCode();
        }
    }

    /**
     * @return array<CriterionCode> List of the criteria according to enabled feature (via feature flag)
     */
    public function getEnabledCriterionCodes(): array
    {
        return $this->allCriteriaFeature->isEnabled()
            ? $this->getAllCriterionCodes()
            : $this->getPartialCriterionCodes();
    }

    /**
     * @return array<CriterionCode> List of all criteria whatever the feature
     */
    public function getAllCriterionCodes(): array
    {
        return $this->allCriteriaCodes;
    }

    /**
     * @return array<CriterionCode> List of the criteria that are not part of the "DQI all criteria" feature
     */
    public function getPartialCriterionCodes(): array
    {
        return $this->partialCriteriaCodes;
    }
}
