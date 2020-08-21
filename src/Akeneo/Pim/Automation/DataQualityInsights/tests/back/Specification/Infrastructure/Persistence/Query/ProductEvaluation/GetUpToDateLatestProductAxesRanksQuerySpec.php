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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class GetUpToDateLatestProductAxesRanksQuerySpec extends ObjectBehavior
{
    public function let(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->beConstructedWith($getLatestProductAxesRanksQuery, $hasUpToDateEvaluationQuery);
    }

    public function it_returns_the_latest_ranks_of_products_with_up_to_date_evaluation(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productIdWithUpToDateEvaluation1 = new ProductId(42);
        $productIdWithUpToDateEvaluation2 = new ProductId(44);
        $productIdWithoutUpToDateEvaluation = new ProductId(123);

        $hasUpToDateEvaluationQuery->forProductIds([
            $productIdWithUpToDateEvaluation1,
            $productIdWithUpToDateEvaluation2,
            $productIdWithoutUpToDateEvaluation
        ])->willReturn([
            $productIdWithUpToDateEvaluation1,
            $productIdWithUpToDateEvaluation2,
        ]);

        $productAxesRanks = [
            42 => new AxisRankCollection(),
            43 => new AxisRankCollection(),
        ];

        $getLatestProductAxesRanksQuery->byProductIds([
            $productIdWithUpToDateEvaluation1,
            $productIdWithUpToDateEvaluation2,
        ])->willReturn($productAxesRanks);

        $this->byProductIds([
            $productIdWithUpToDateEvaluation1,
            $productIdWithUpToDateEvaluation2,
            $productIdWithoutUpToDateEvaluation
        ])->shouldReturn($productAxesRanks);
    }

    public function it_returns_an_empty_array_if_no_product_has_an_up_to_date_evaluation(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productIds = [
            new ProductId(42),
            new ProductId(43),
        ];

        $hasUpToDateEvaluationQuery->forProductIds($productIds)->willReturn([]);
        $getLatestProductAxesRanksQuery->byProductIds(Argument::any())->shouldNotBeCalled();

        $this->byProductIds($productIds)->shouldReturn([]);
    }
}
