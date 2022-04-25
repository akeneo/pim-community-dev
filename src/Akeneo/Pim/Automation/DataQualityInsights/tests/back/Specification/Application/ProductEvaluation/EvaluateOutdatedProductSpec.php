<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateOutdatedProductSpec extends ObjectBehavior
{
    public function let(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProducts $evaluateProducts,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $this->beConstructedWith($hasUpToDateEvaluationQuery, $evaluateProducts, $idFactory);
    }

    public function it_evaluate_a_product_if_it_has_outdated_evaluation(
        $hasUpToDateEvaluationQuery,
        $evaluateProducts,
        $idFactory
    ) {
        $productId = new ProductId(42);
        $collection = ProductIdCollection::fromString('42');

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(false);
        $idFactory->createCollection(['42'])->willReturn($collection);
        $evaluateProducts->__invoke($collection)->shouldBeCalled();

        $this->__invoke($productId);
    }

    public function it_does_not_evaluate_a_product_with_up_to_date_evaluation(
        $hasUpToDateEvaluationQuery,
        $evaluateProducts,
        $idFactory
    ) {
        $productId = new ProductId(42);
        $collection = ProductIdCollection::fromString('42');

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(true);
        $idFactory->createCollection(['42'])->willReturn($collection);
        $evaluateProducts->__invoke($collection)->shouldNotBeCalled();

        $this->__invoke($productId);
    }
}
