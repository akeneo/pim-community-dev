<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Value\OptionsValueInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Component\Catalog\Model\ValueInterface;

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

    function it_supports_standard_format_and_product_value(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'standard')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
    }

    function it_normalizes_a_product_value_in_standard_format_with_no_locale_and_no_scope(
        SerializerInterface $serializer,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('product_value_data', null, ['is_decimals_allowed' => false])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $value->getData()->willReturn('product_value_data');
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_product_value_in_standard_format_with_locale_and_no_scope(
        SerializerInterface $serializer,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('product_value_data', null, ['is_decimals_allowed' => false])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $value->getData()->willReturn('product_value_data');
        $value->getLocale()->willReturn('en_US');
        $value->getScope()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => null,
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_product_value_in_standard_format_with_locale_and_scope(
        SerializerInterface $serializer,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('product_value_data', null, ['is_decimals_allowed' => false])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $value->getData()->willReturn('product_value_data');
        $value->getLocale()->willReturn('en_US');
        $value->getScope()->willReturn('ecommerce');
        $value->getAttribute()->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_number_product_value_with_decimal(
        SerializerInterface $serializer,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('15.50', null, ['is_decimals_allowed' => true])
            ->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $value->getData()->willReturn('15.50');
        $value->getLocale()->willReturn('en_US');
        $value->getScope()->willReturn('ecommerce');
        $value->getAttribute()->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $attribute->isDecimalsAllowed()->willReturn(true);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => '15.5000',
            ]
        );
    }

    function it_normalizes_a_number_product_value_without_decimal(
        SerializerInterface $serializer,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('15.00', null, [])
            ->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $value->getData()->willReturn('15.00');
        $value->getLocale()->willReturn('en_US');
        $value->getScope()->willReturn('ecommerce');
        $value->getAttribute()->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 15,
            ]
        );
    }

    function it_normalizes_a_simple_select(
        SerializerInterface $serializer,
        ValueInterface $value,
        AttributeInterface $attribute,
        AttributeOptionInterface $simpleSelect
    ) {
        $simpleSelect->getCode()->willReturn('optionA');
        $serializer->normalize($simpleSelect, null, [])->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $value->getData()->willReturn($simpleSelect);
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => 'optionA',
            ]
        );
    }

    function it_normalizes_a_multi_select(
        SerializerInterface $serializer,
        OptionsValueInterface $value,
        AttributeInterface $attribute,
        AttributeOptionInterface $multiSelect
    ) {
        $multiSelect->getCode()->willReturn('optionA');
        $serializer->normalize($multiSelect, null, [])->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $values = [$multiSelect];

        $value->getData()->willReturn($values);
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => ['optionA'],
            ]
        );
    }
}
