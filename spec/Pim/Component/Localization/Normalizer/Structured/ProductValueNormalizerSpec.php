<?php

namespace spec\Pim\Component\Localization\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Localization\Localizer\DateLocalizer;
use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
use Pim\Component\Localization\Localizer\NumberLocalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $productValueNormalizer,
        LocalizerRegistryInterface $localizerRegistry
    ) {
        $this->beConstructedWith($productValueNormalizer, $localizerRegistry);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_type(ProductValueInterface $productValue)
    {
        $this->supportsNormalization($productValue, 'xml')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'json')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'csv')->shouldReturn(false);
        $this->supportsNormalization($productValue, 'flat')->shouldReturn(false);
    }

    function it_normalizes_number_with_decimal(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        NumberLocalizer $numberLocalizer
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn(25.3);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_number')->willReturn($numberLocalizer);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['data' => '25.30']);
        $numberLocalizer->localize('25.30', $options)->willReturn('25,30');
    }

    function it_normalizes_number_without_decimal(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        NumberLocalizer $numberLocalizer
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn(25);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_number')->willReturn($numberLocalizer);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['data' => '25']);
        $numberLocalizer->localize('25', $options)->willReturn('25');

        $this->normalize($productValue, null, $options)->shouldReturn(['data' => '25']);
    }

    function it_normalizes_number_without_decimal_as_string(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        NumberLocalizer $numberLocalizer
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn('25');
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_number')->willReturn($numberLocalizer);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['data' => '25']);
        $numberLocalizer->localize('25', $options)->willReturn('25');

        $this->normalize($productValue, null, $options)->shouldReturn(['data' => '25']);
    }

    function it_normalizes_null_number(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        NumberLocalizer $numberLocalizer
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn(null);
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_number')->willReturn($numberLocalizer);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['data' => '']);
        $numberLocalizer->localize('', $options)->willReturn('');

        $this->normalize($productValue, null, $options)->shouldReturn(['data' => '']);
    }

    function it_normalizes_empty_number(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        NumberLocalizer $numberLocalizer
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn('');
        $attribute->getAttributeType()->willReturn(AttributeTypes::NUMBER);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_number')->willReturn($numberLocalizer);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['data' => '']);
        $numberLocalizer->localize('', $options)->willReturn('');

        $this->normalize($productValue, null, $options)->shouldReturn(['data' => '']);
    }

    function it_normalizes_product_value_which_is_not_a_number(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        NumberLocalizer $numberLocalizer
    ) {
        $options = ['decimal_separator' => ','];
        $productValue->getData()->willReturn('shoes');
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_number')->willReturn($numberLocalizer);
        $localizerRegistry->getLocalizer(Argument::any())->willReturn(null);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['simple-select' => 'shoes']);
        $numberLocalizer->localize('', $options)->shouldNotBeCalled();

        $this->normalize($productValue, null, $options)->shouldReturn(['simple-select' => 'shoes']);
    }

    function it_normalizes_date(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        DateLocalizer $dateLocalizer
    ) {
        $options = ['date_format' => 'd/m/Y'];
        $productValue->getData()->willReturn(new \DateTime('2000-10-28'));
        $attribute->getAttributeType()->willReturn(AttributeTypes::DATE);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_date')->willReturn($dateLocalizer);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['data' => '2000-10-28']);
        $dateLocalizer->localize('2000-10-28', $options)->willReturn('28/10/2000');

        $this->normalize($productValue, null, $options)->shouldReturn(['data' => '28/10/2000']);
    }

    function it_normalizes_empty_date(
        $productValueNormalizer,
        $localizerRegistry,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        DateLocalizer $dateLocalizer
    ) {
        $options = ['date_format' => 'd/m/Y'];
        $productValue->getData()->willReturn('');
        $attribute->getAttributeType()->willReturn(AttributeTypes::DATE);
        $productValue->getAttribute()->willReturn($attribute);
        $localizerRegistry->getLocalizer('pim_catalog_date')->willReturn($dateLocalizer);

        $productValueNormalizer->normalize($productValue, null, $options)->willReturn(['data' => '']);
        $dateLocalizer->localize('', $options)->willReturn('');

        $this->normalize($productValue, null, $options)->shouldReturn(['data' => '']);
    }
}
