<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Persistence;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Pim\Bundle\CatalogBundle\Model;

class ProductChangesApplierSpec extends ObjectBehavior
{
    public function let(DenormalizerInterface $denormalizer)
    {
        $this->beConstructedWith($denormalizer);
    }

    function it_uses_denormlizer_to_apply_data_to_a_product_value(
        $denormalizer,
        Model\AbstractProduct $product,
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $product->getValue('foo', null, null)->willReturn($value);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('some_type');

        $denormalizer->denormalize('bar', 'some_type', null, ['instance' => $value])->shouldBeCalled();

        $this->apply($product, 'foo', 'bar');
    }

    function it_uses_denormlizer_to_apply_data_to_a_localizable_and_scopable_product_value(
        $denormalizer,
        Model\AbstractProduct $product,
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $product->getValue('foo', 'en_US', 'print')->willReturn($value);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('some_type');

        $denormalizer->denormalize('bar', 'some_type', null, ['instance' => $value])->shouldBeCalled();

        $this->apply($product, 'foo-en_US-print', 'bar');
    }

    function it_ignores_unresolvable_product_value(
        $denormalizer,
        Model\AbstractProduct $product
    ) {
        $product->getValue('foo', 'en_US', 'print')->willReturn(null);

        $denormalizer->denormalize(Argument::cetera())->shouldNotBeCalled();

        $this->apply($product, 'foo-en_US-print', 'bar');
    }
}
