<?php

namespace spec\Pim\Component\Localization\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $productValueNormalizer, LocalizerInterface $localizer)
    {
        $this->beConstructedWith($productValueNormalizer, $localizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_type(ProductValueInterface $productValue)
    {
        $this->supportsNormalization($productValue, 'csv')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'flat')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'versioning')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'versioning')->shouldReturn(false);
    }

    function it_normalizes_number_with_decimal(
        $productValueNormalizer,
        $localizer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn(25.3);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['number' => '25.30']);
        $localizer->convertDefaultToLocalized('25.30', $options)->willReturn('25,30');

        $this->normalize($productValue, null, $options)->shouldReturn(['number' => '25,30']);
    }

    function it_normalizes_number_without_decimal(
        $productValueNormalizer,
        $localizer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn(25);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['number' => '25']);
        $localizer->convertDefaultToLocalized('25', $options)->willReturn('25');

        $this->normalize($productValue, null, $options)->shouldReturn(['number' => '25']);
    }

    function it_normalizes_number_without_decimal_as_string(
        $productValueNormalizer,
        $localizer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn('25');
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['number' => '25']);
        $localizer->convertDefaultToLocalized('25', $options)->willReturn('25');

        $this->normalize($productValue, null, $options)->shouldReturn(['number' => '25']);
    }

    function it_normalizes_null_number(
        $productValueNormalizer,
        $localizer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn(null);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['number' => '']);
        $localizer->convertDefaultToLocalized('', $options)->willReturn('');

        $this->normalize($productValue, null, $options)->shouldReturn(['number' => '']);
    }

    function it_normalizes_empty_number(
        $productValueNormalizer,
        $localizer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn('');
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['number' => '']);
        $localizer->convertDefaultToLocalized('', $options)->willReturn('');

        $this->normalize($productValue, null, $options)->shouldReturn(['number' => '']);
    }

    function it_normalizes_product_value_which_is_not_a_number(
        $productValueNormalizer,
        $localizer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn('shoes');
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT);
        $productValue->getAttribute()->willReturn($attribute);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['simple-select' => 'shoes']);
        $localizer->convertDefaultToLocalized('', $options)->shouldNotBeCalled();

        $this->normalize($productValue, null, $options)->shouldReturn(['simple-select' => 'shoes']);
    }
}
