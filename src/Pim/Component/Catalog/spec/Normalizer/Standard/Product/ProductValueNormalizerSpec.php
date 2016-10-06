<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Prophecy\Promise\ReturnPromise;
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
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('product_value_data', null, [])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('product_value_data');
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT);

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
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('product_value_data', null, [])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('product_value_data');
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn(null);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT);

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
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('product_value_data', null, [])
            ->shouldBeCalled()
            ->willReturn('product_value_data');
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('product_value_data');
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn('ecommerce');
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT);

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_number_product_value_with_decimal(
        SerializerInterface $serializer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('15.50', null, [])
            ->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('15.50');
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn('ecommerce');
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => '15.5000',
            ]
        );
    }

    function it_normalizes_a_number_product_value_without_decimal(
        SerializerInterface $serializer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $serializer->normalize('15.00', null, [])
            ->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn('15.00');
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn('ecommerce');
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 15,
            ]
        );
    }

    function it_normalizes_a_simple_select(
        SerializerInterface $serializer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        AttributeOptionInterface $simpleSelect
    ) {
        $simpleSelect->getCode()->willReturn('optionA');
        $serializer->normalize($simpleSelect, null, [])->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $productValue->getData()->willReturn($simpleSelect);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => 'optionA',
            ]
        );
    }

    function it_normalizes_a_multi_select(
        SerializerInterface $serializer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        AttributeOptionInterface $multiSelect,
        ArrayCollection $values,
        \ArrayIterator $iterator
    ) {
        $multiSelect->getCode()->willReturn('optionA');
        $serializer->normalize($multiSelect, null, [])->shouldNotBeCalled();
        $this->setSerializer($serializer);

        $values->getIterator()->willReturn($iterator);
        $iterator->rewind()->willReturn($multiSelect);
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($multiSelect);
        $iterator->next()->willReturn(null);

        $productValue->getData()->willReturn($values);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);

        $this->normalize($productValue)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => ['optionA'],
            ]
        );
    }
}
