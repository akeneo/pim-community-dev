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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class MarkCriteriaToEvaluateSpec extends ObjectBehavior
{
    public function let(
        GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery,
        GetProductIdsImpactedByAttributeGroupActivationQueryInterface $getProductIdsImpactedByAttributeGroupActivationQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->beConstructedWith(
            $getUpdatedProductIdsQuery,
            $getProductIdsImpactedByAttributeGroupActivationQuery,
            $createProductsCriteriaEvaluations
        );
    }

    public function it_creates_missing_criterion_evaluations_for_products_updated_since_a_given_date(
        $getUpdatedProductIdsQuery,
        $getProductIdsImpactedByAttributeGroupActivationQuery,
        $createProductsCriteriaEvaluations
    ) {
        $updatedSince = new \DateTimeImmutable();
        $updatedProductIdsBatch1 = [new ProductId(42), new ProductId(123)];
        $updatedProductIdsBatch2 = [new ProductId(321)];

        $getUpdatedProductIdsQuery->since($updatedSince, 2)->willReturn(
            new \ArrayIterator([$updatedProductIdsBatch1, $updatedProductIdsBatch2])
        );

        $createProductsCriteriaEvaluations->createAll($updatedProductIdsBatch1)->shouldBeCalled();
        $createProductsCriteriaEvaluations->createAll($updatedProductIdsBatch2)->shouldBeCalled();

        $impactedProductIdsBatch = [new ProductId(24), new ProductId(654)];
        $getProductIdsImpactedByAttributeGroupActivationQuery->updatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$impactedProductIdsBatch])
        );

        $createProductsCriteriaEvaluations->createAll($impactedProductIdsBatch)->shouldBeCalled();

        $this->forUpdatesSince($updatedSince, 2);
    }
}
