<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProductModelsSpec extends ObjectBehavior
{
    public function let(
        EvaluatePendingCriteria $evaluatePendingProductModelCriteria,
        EvaluateCriteria $evaluateCriteria,
        ConsolidateProductModelScores $consolidateProductScores,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ): void
    {
        $this->beConstructedWith($evaluatePendingProductModelCriteria, $evaluateCriteria, $consolidateProductScores, $eventDispatcher, $logger);
    }

    public function it_evaluates_product_models_fetching_pending_criteria($evaluatePendingProductModelCriteria, $consolidateProductScores, $eventDispatcher): void
    {
        $productModelIdCollection = ProductModelIdCollection::fromStrings(['123', '321']);
        $evaluatePendingProductModelCriteria->evaluateAllCriteria($productModelIdCollection)->shouldBeCalled();
        $consolidateProductScores->consolidate($productModelIdCollection)->shouldBeCalledOnce();
        $eventDispatcher->dispatch(Argument::that(static function ($event) use ($productModelIdCollection) {
            return $event instanceof ProductModelsEvaluated && $event->getProductModelIds() === $productModelIdCollection;
        }))->shouldBeCalledOnce();
        $this->forPendingCriteria($productModelIdCollection);
    }

    public function it_evaluates_product_models(
        EvaluateCriteria $evaluateCriteria,
        ConsolidateProductModelScores $consolidateProductScores,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $productModelIdCollection = ProductModelIdCollection::fromStrings(['123', '321']);
        $productCriterionCodes = [new CriterionCode('1')];

        $evaluateCriteria->forEntityIds($productModelIdCollection, $productCriterionCodes)->shouldBeCalledOnce();
        $consolidateProductScores->consolidate($productModelIdCollection)->shouldBeCalledOnce();
        $eventDispatcher->dispatch(Argument::that(static function ($event) use ($productModelIdCollection) {
            return $event instanceof ProductModelsEvaluated && $event->getProductModelIds() === $productModelIdCollection;
        }))->shouldBeCalledOnce();
        $this->forCriteria($productModelIdCollection, $productCriterionCodes);
    }
}
