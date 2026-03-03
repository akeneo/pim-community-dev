<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterPartialCriteriaEvaluationsSpec extends ObjectBehavior
{
    public function let(CriteriaByFeatureRegistry $criteriaRegistry)
    {
        $criteriaRegistry->getPartialCriterionCodes()->willReturn([
            new CriterionCode('criterion_partial_A'),
            new CriterionCode('criterion_partial_B'),
        ]);

        $this->beConstructedWith($criteriaRegistry);
    }

    public function it_filters_criteria_evaluations_for_partial_score()
    {
        $criterionEvaluationPartialA = $this->buildCriterionEvaluation('criterion_partial_A');
        $criterionEvaluationPartialB = $this->buildCriterionEvaluation('criterion_partial_B');
        $criterionEvaluationAll = $this->buildCriterionEvaluation('criterion_all');

        $criteriaEvaluations = (new Read\CriterionEvaluationCollection())
            ->add($criterionEvaluationPartialA)
            ->add($criterionEvaluationAll)
            ->add($criterionEvaluationPartialB);

        $expectedCriteriaEvaluations = (new Read\CriterionEvaluationCollection())
            ->add($criterionEvaluationPartialA)
            ->add($criterionEvaluationPartialB);

        $this->__invoke($criteriaEvaluations)->shouldBeLike($expectedCriteriaEvaluations);
    }

    private function buildCriterionEvaluation(string $criterionCode): Read\CriterionEvaluation
    {
        return new Read\CriterionEvaluation(
            new CriterionCode($criterionCode),
            ProductUuid::fromString(Uuid::uuid4()->toString()),
            null,
            CriterionEvaluationStatus::pending(),
            null
        );
    }
}
