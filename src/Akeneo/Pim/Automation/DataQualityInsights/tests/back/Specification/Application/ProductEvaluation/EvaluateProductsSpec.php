<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProductsSpec extends ObjectBehavior
{
    public function let(
        EvaluatePendingCriteria $evaluatePendingProductCriteria,
        EvaluateCriteria $evaluateCriteria,
        ConsolidateProductScores $consolidateProductScores,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith($evaluatePendingProductCriteria, $evaluateCriteria, $consolidateProductScores, $eventDispatcher);
    }

    public function it_evaluates_products(
        EvaluateCriteria $evaluateCriteria,
        ConsolidateProductScores $consolidateProductScores,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $productUuidCollection = ProductUuidCollection::fromStrings([(Uuid::uuid4())->toString()]);
        $productCriterionCodes = [new CriterionCode('1')];

        $evaluateCriteria->forEntityIds($productUuidCollection, $productCriterionCodes)->shouldBeCalledOnce();
        $consolidateProductScores->consolidate($productUuidCollection)->shouldBeCalledOnce();
        $eventDispatcher->dispatch(Argument::any())->shouldBeCalledOnce();

        $this->forCriteria($productUuidCollection, $productCriterionCodes);
    }
}
