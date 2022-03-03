<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateOutdatedProductModelSpec extends ObjectBehavior
{
    public function let(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProductModels $evaluateProductModels
    ) {
        $this->beConstructedWith($hasUpToDateEvaluationQuery, $evaluateProductModels);
    }

    public function it_evaluate_a_product_model_if_it_has_outdated_evaluation(
        $hasUpToDateEvaluationQuery,
        $evaluateProductModels
    ) {
        $productModelId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productModelId)->willReturn(false);
        $evaluateProductModels->__invoke(ProductIdCollection::fromProductId($productModelId))->shouldBeCalled();

        $this->__invoke($productModelId);
    }

    public function it_does_not_evaluate_a_product_model_with_up_to_date_evaluation(
        $hasUpToDateEvaluationQuery,
        $evaluateProductModels
    ) {
        $productModelId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productModelId)->willReturn(true);
        $evaluateProductModels->__invoke(ProductIdCollection::fromProductId($productModelId))->shouldNotBeCalled();

        $this->__invoke($productModelId);
    }
}
