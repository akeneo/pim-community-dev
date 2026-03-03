<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterPartialCriteriaEvaluations
{
    public function __construct(
        private CriteriaByFeatureRegistry $criteriaRegistry,
    ) {
    }

    public function __invoke(Read\CriterionEvaluationCollection $criteriaEvaluations): Read\CriterionEvaluationCollection
    {
        $partialCriteriaCodes = $this->criteriaRegistry->getPartialCriterionCodes();
        $partialCriteriaEvaluations = new Read\CriterionEvaluationCollection();

        /** @var Read\CriterionEvaluation $criteriaEvaluation */
        foreach ($criteriaEvaluations as $criteriaEvaluation) {
            if (in_array($criteriaEvaluation->getCriterionCode(), $partialCriteriaCodes)) {
                $partialCriteriaEvaluations->add($criteriaEvaluation);
            }
        }

        return $partialCriteriaEvaluations;
    }
}
