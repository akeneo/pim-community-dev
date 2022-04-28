<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateOutdatedProductSpec extends ObjectBehavior
{
    public function let(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProducts $evaluateProducts,
        ProductUuidFactory $uuidFactory
    ) {
        $this->beConstructedWith($hasUpToDateEvaluationQuery, $evaluateProducts, $uuidFactory);
    }

    public function it_evaluate_a_product_if_it_has_outdated_evaluation(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProducts $evaluateProducts,
        ProductEntityIdFactoryInterface $uuidFactory
    ) {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $collection = ProductUuidCollection::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $hasUpToDateEvaluationQuery->forEntityId($productUuid)->willReturn(false);
        $uuidFactory->createCollection(['df470d52-7723-4890-85a0-e79be625e2ed'])->willReturn($collection);
        $evaluateProducts->__invoke($collection)->shouldBeCalled();

        $this->__invoke($productUuid);
    }

    public function it_does_not_evaluate_a_product_with_up_to_date_evaluation(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        EvaluateProducts $evaluateProducts,
        ProductEntityIdFactoryInterface $uuidFactory
    ) {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $collection = ProductUuidCollection::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $hasUpToDateEvaluationQuery->forEntityId($productUuid)->willReturn(true);
        $uuidFactory->createCollection(['df470d52-7723-4890-85a0-e79be625e2ed'])->willReturn($collection);
        $evaluateProducts->__invoke($collection)->shouldNotBeCalled();

        $this->__invoke($productUuid);
    }
}
