<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class GetUpToDateLatestCriteriaEvaluationsByProductIdQuerySpec extends ObjectBehavior
{
    public function let(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->beConstructedWith($getLatestCriteriaEvaluationsByProductIdQuery, $hasUpToDateEvaluationQuery);
    }

    public function it_returns_the_latest_criteria_evaluations_if_the_evaluation_of_the_product_is_up_to_date(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productId = new ProductId(42);
        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(true);

        $criteriaEvaluations = (new Read\CriterionEvaluationCollection())
            ->add(new Read\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('spelling'),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending(),
                null,
                null,
                null
        ));

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($criteriaEvaluations);

        $this->execute($productId)->shouldReturn($criteriaEvaluations);
    }

    public function it_returns_empty_criteria_evaluations_if_the_evaluation_of_the_product_is_outdated(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productId = new ProductId(42);
        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(false);

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->shouldNotBeCalled();

        $this->execute($productId)->shouldBeLike(new Read\CriterionEvaluationCollection());
    }
}
