<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use Symfony\Component\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\Product\ProductValueNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_standard_format_and_product_value(ProductValueInterface $productValue)
    {
        $otherObject = [];

        $this->supportsNormalization($productValue, 'standard')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'other_format')->shouldReturn(false);
        $this->supportsNormalization($otherObject, 'other_format')->shouldReturn(false);
        $this->supportsNormalization($otherObject, 'standard')->shouldReturn(false);
    }

    function it_normalizes_a_product_value_in_standard_format_with_no_locale_and_no_scope(
        SerializerInterface $serializer,
        ProductValueInterface $productValue
    ) {
        $serializer->normalize('product_value_data', null, [])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('product_value_data');
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_product_value_in_standard_format_with_locale_and_no_scope(
        SerializerInterface $serializer,
        ProductValueInterface $productValue
    ) {
        $serializer->normalize('product_value_data', null, [])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('product_value_data');
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn(null);

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => null,
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_product_value_in_standard_format_with_locale_and_scope(
        SerializerInterface $serializer,
        ProductValueInterface $productValue
    ) {
        $serializer->normalize('product_value_data', null, [])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('product_value_data');
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn('ecommerce');

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 'product_value_data',
            ]
        );
    }
}
