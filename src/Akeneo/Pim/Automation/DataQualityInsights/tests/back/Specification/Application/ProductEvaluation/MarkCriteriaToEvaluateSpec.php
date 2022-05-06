<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

    public function it_marks_criteria_to_evaluate_for_updates_since_a_given_date(
        $getUpdatedProductIdsQuery,
        $getProductIdsImpactedByAttributeGroupActivationQuery,
        $createProductsCriteriaEvaluations
    ) {
        $updatedSince = new \DateTimeImmutable();
        $updatedProductIdsBatch1 = ProductIdCollection::fromInts([42, 123]);
        $updatedProductIdsBatch2 = ProductIdCollection::fromInt(321);

        $getUpdatedProductIdsQuery->since($updatedSince, 2)->willReturn(
            new \ArrayIterator([$updatedProductIdsBatch1, $updatedProductIdsBatch2])
        );

        $createProductsCriteriaEvaluations->createAll($updatedProductIdsBatch1)->shouldBeCalled();
        $createProductsCriteriaEvaluations->createAll($updatedProductIdsBatch2)->shouldBeCalled();

        $impactedProductIdsBatch = ProductIdCollection::fromInts([24, 654]);
        $getProductIdsImpactedByAttributeGroupActivationQuery->updatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$impactedProductIdsBatch])
        );

        $createProductsCriteriaEvaluations->createAll($impactedProductIdsBatch)->shouldBeCalled();

        $this->forUpdatesSince($updatedSince, 2);
    }
}
