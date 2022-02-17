<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProductModelsSpec extends ObjectBehavior
{
    public function let(
        EvaluatePendingCriteria $evaluatePendingProductModelCriteria,
        ConsolidateProductScores $consolidateProductScores,
        EventDispatcherInterface $eventDispatcher
    ): void
    {
        $this->beConstructedWith($evaluatePendingProductModelCriteria, $consolidateProductScores, $eventDispatcher);
    }

    public function it_evaluates_product_models($evaluatePendingProductModelCriteria, $consolidateProductScores, $eventDispatcher): void
    {
        $productModelIdCollection = ProductIdCollection::fromInts([1, 2]);
        $evaluatePendingProductModelCriteria->evaluateAllCriteria($productModelIdCollection)->shouldBeCalled();
        $consolidateProductScores->consolidate($productModelIdCollection)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::that(static function ($event) use ($productModelIdCollection) {
            return $event instanceof ProductModelsEvaluated && $event->getProductModelIds() === $productModelIdCollection;
        }));
        $this->__invoke($productModelIdCollection);
    }
}