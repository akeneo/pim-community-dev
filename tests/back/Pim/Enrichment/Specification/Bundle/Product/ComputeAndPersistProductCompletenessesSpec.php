<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ComputeAndPersistProductCompletenessesSpec extends ObjectBehavior
{
    function let(
        CompletenessCalculatorInterface $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $this->beConstructedWith($completenessCalculator, $saveProductCompletenesses);
    }

    function it_is_a_compute_product_completeness_event_subscriber()
    {
        $this->shouldHaveType(ComputeAndPersistProductCompletenesses::class);
    }

    function it_computes_and_saves_completenesses_for_a_product(
        CompletenessCalculatorInterface $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $product = new Product();
        $product->setId(42);
        $product->setIdentifier(ScalarValue::value('identifier', 'a-product'));

        $completenessCalculator->fromProductIdentifiers(['product_1'])->willReturn(
            [
                new ProductCompletenessCollection(
                    42,
                    [new ProductCompleteness('ecommerce', 'en_US', 1, [])]
                )
            ]
        )->shouldBeCalled();
        $saveProductCompletenesses->save(Argument::type(ProductCompletenessCollection::class))->shouldBeCalled();

        $this->fromProductIdentifier('product_1');
    }
}
