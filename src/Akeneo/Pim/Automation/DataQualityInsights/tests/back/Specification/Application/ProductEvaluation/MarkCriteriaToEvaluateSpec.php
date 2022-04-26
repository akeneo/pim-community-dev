<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductUuidsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MarkCriteriaToEvaluateSpec extends ObjectBehavior
{
    public function let(
        GetUpdatedProductUuidsQueryInterface                         $getUpdatedProductUuidsQuery,
        GetEntityIdsImpactedByAttributeGroupActivationQueryInterface $getProductIdsImpactedByAttributeGroupActivationQuery,
        CreateCriteriaEvaluations                                    $createProductsCriteriaEvaluations
    ) {
        $this->beConstructedWith(
            $getUpdatedProductUuidsQuery,
            $getProductIdsImpactedByAttributeGroupActivationQuery,
            $createProductsCriteriaEvaluations
        );
    }

    public function it_marks_criteria_to_evaluate_for_updates_since_a_given_date(
        GetUpdatedProductUuidsQueryInterface                         $getUpdatedProductUuidsQuery,
        GetEntityIdsImpactedByAttributeGroupActivationQueryInterface $getProductIdsImpactedByAttributeGroupActivationQuery,
        CreateCriteriaEvaluations                                    $createProductsCriteriaEvaluations
    ) {
        $updatedSince = new \DateTimeImmutable();
        $updatedProductIdsBatch1 = ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed', '6d125b99-d971-41d9-a264-b020cd486aee']);
        $updatedProductIdsBatch2 = ProductUuidCollection::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2');

        $getUpdatedProductUuidsQuery->since($updatedSince, 2)->willReturn(
            new \ArrayIterator([$updatedProductIdsBatch1, $updatedProductIdsBatch2])
        );

        $createProductsCriteriaEvaluations->createAll($updatedProductIdsBatch1)->shouldBeCalled();
        $createProductsCriteriaEvaluations->createAll($updatedProductIdsBatch2)->shouldBeCalled();

        $impactedProductIdsBatch = ProductUuidCollection::fromStrings(['ac930366-36f2-4ad9-9a9f-de94c913d8ca', '677650b2-e2eb-4491-a193-5b3314c0499f']);
        $getProductIdsImpactedByAttributeGroupActivationQuery->updatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$impactedProductIdsBatch])
        );

        $createProductsCriteriaEvaluations->createAll($impactedProductIdsBatch)->shouldBeCalled();

        $this->forUpdatesSince($updatedSince, 2);
    }
}
