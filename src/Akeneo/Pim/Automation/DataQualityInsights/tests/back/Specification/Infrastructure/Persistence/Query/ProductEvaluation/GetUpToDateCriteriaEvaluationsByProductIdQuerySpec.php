<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUpToDateCriteriaEvaluationsByProductIdQuerySpec extends ObjectBehavior
{
    public function let(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->beConstructedWith($getCriteriaEvaluationsByProductIdQuery, $hasUpToDateEvaluationQuery);
    }

    public function it_returns_criteria_evaluations_if_the_evaluation_of_the_product_is_up_to_date(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productId = new ProductId(42);
        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(true);

        $criteriaEvaluations = (new Read\CriterionEvaluationCollection())
            ->add(new Read\CriterionEvaluation(
                new CriterionCode('spelling'),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending(),
                null
        ));

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($criteriaEvaluations);

        $this->execute($productId)->shouldReturn($criteriaEvaluations);
    }

    public function it_returns_empty_criteria_evaluations_if_the_evaluation_of_the_product_is_outdated(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productId = new ProductId(42);
        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(false);

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->shouldNotBeCalled();

        $this->execute($productId)->shouldBeLike(new Read\CriterionEvaluationCollection());
    }
}
