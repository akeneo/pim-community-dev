<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Persistence;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\Serialization\FlatProductValueDenormalizer;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class ProductChangesApplierSpec extends ObjectBehavior
{
    public function let(FlatProductValueDenormalizer $denormalizer)
    {
        $this->beConstructedWith($denormalizer);
    }

    function it_uses_denormlizer_to_apply_data_to_a_product_value(
        $denormalizer,
        AbstractProduct $product,
        AbstractProductValue $value
    ) {
        $product->getValue('foo', null, null)->willReturn($value);

        $denormalizer->denormalize('bar', get_class($value->getWrappedObject()), 'csv', ['instance' => $value])->shouldBeCalled();

        $this->apply($product, 'foo', 'bar');
    }

    function it_uses_denormlizer_to_apply_data_to_a_localizable_and_scopable_product_value(
        $denormalizer,
        AbstractProduct $product,
        AbstractProductValue $value
    ) {
        $product->getValue('foo', 'en_US', 'print')->willReturn($value);

        $denormalizer->denormalize('bar', get_class($value->getWrappedObject()), 'csv', ['instance' => $value])->shouldBeCalled();

        $this->apply($product, 'foo-en_US-print', 'bar');
    }

    function it_ignores_unresolvable_product_value(
        $denormalizer,
        AbstractProduct $product
    ) {
        $product->getValue('foo', 'en_US', 'print')->willReturn(null);

        $denormalizer->denormalize(Argument::cetera())->shouldNotBeCalled();

        $this->apply($product, 'foo-en_US-print', 'bar');
    }
}
