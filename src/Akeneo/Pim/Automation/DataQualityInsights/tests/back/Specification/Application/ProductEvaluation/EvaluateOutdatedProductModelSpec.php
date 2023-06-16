<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateOutdatedProductModelSpec extends ObjectBehavior
{
    public function let(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProductModels $evaluateProductModels,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $this->beConstructedWith($hasUpToDateEvaluationQuery, $evaluateProductModels, $idFactory);
    }

    public function it_evaluate_a_product_model_if_it_has_outdated_evaluation(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProductModels $evaluateProductModels,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $productModelId = new ProductModelId(42);
        $collection = ProductModelIdCollection::fromStrings(['42']);

        $hasUpToDateEvaluationQuery->forEntityId($productModelId)->willReturn(false);
        $idFactory->createCollection(['42'])->willReturn($collection);
        $evaluateProductModels->forPendingCriteria($collection)->shouldBeCalled();

        $this->__invoke($productModelId);
    }

    public function it_does_not_evaluate_a_product_model_with_up_to_date_evaluation(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProductModels $evaluateProductModels,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $productModelId = new ProductModelId(42);
        $collection = ProductModelIdCollection::fromStrings(['42']);

        $hasUpToDateEvaluationQuery->forEntityId($productModelId)->willReturn(true);
        $idFactory->createCollection(['42'])->willReturn($collection);
        $evaluateProductModels->forPendingCriteria($collection)->shouldNotBeCalled();

        $this->__invoke($productModelId);
    }
}
