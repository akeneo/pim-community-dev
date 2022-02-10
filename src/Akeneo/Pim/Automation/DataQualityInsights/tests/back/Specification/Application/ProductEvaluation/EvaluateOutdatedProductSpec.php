<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateOutdatedProductSpec extends ObjectBehavior
{
    public function let(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProducts $evaluateProducts
    ) {
        $this->beConstructedWith($hasUpToDateEvaluationQuery, $evaluateProducts);
    }

    public function it_evaluate_a_product_if_it_has_outdated_evaluation(
        $hasUpToDateEvaluationQuery,
        $evaluateProducts
    ) {
        $productId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(false);
        $evaluateProducts->__invoke([42])->shouldBeCalled();

        $this->__invoke($productId);
    }

    public function it_does_not_evaluate_a_product_with_up_to_date_evaluation(
        $hasUpToDateEvaluationQuery,
        $evaluateProducts
    ) {
        $productId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(true);
        $evaluateProducts->__invoke([42])->shouldNotBeCalled();

        $this->__invoke($productId);
    }
}
