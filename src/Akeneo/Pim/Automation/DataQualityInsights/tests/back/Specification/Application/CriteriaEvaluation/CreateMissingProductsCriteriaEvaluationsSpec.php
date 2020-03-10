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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class CreateMissingProductsCriteriaEvaluationsSpec extends ObjectBehavior
{
    public function let(
        GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface $getUpdatedProductsWithoutUpToDateEvaluationQuery,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->beConstructedWith($getUpdatedProductsWithoutUpToDateEvaluationQuery, $createProductsCriteriaEvaluations);
    }

    public function it_creates_missing_criterion_evaluations_for_products_updated_since_a_given_date(
        GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface $getUpdatedProductsWithoutUpToDateEvaluationQuery,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $updatedSince = new \DateTimeImmutable();
        $productIdsBatch1 = [new ProductId(42), new ProductId(123)];
        $productIdsBatch2 = [new ProductId(321)];

        $getUpdatedProductsWithoutUpToDateEvaluationQuery->execute($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch1, $productIdsBatch2])
        );

        $createProductsCriteriaEvaluations->create($productIdsBatch1)->shouldBeCalled();
        $createProductsCriteriaEvaluations->create($productIdsBatch2)->shouldBeCalled();

        $this->createForProductsUpdatedSince($updatedSince, 2);
    }
}
