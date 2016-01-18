<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, AttributeInterface $simpleAttribute)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);

        $simpleAttribute->isLocalizable()->willReturn(false);
        $simpleAttribute->isScopable()->willReturn(false);
        $simpleAttribute->getCode()->willReturn('simple');
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_csv_normalization_of_product_value(ProductValueInterface $value)
    {
        $this->supportsNormalization($value, 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_product(ProductValueInterface $value)
    {
        $this->supportsNormalization($value, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_value_with_null_data(ProductValueInterface $value, AttributeInterface $simpleAttribute)
    {
        $value->getData()->willReturn(null);
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '']);
    }

    function it_normalizes_a_value_with_a_integer_data(ProductValueInterface $value, AttributeInterface $simpleAttribute)
    {
        $value->getData()->willReturn(12);
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12']);
    }

    function it_normalizes_a_value_with_a_float_data_with_decimals_allowed(ProductValueInterface $value, AttributeInterface $simpleAttribute)
    {
        $value->getData()->willReturn('12.2500');
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('decimal');
        $simpleAttribute->isDecimalsAllowed()->willReturn(true);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12.2500']);
    }

    function it_normalizes_a_value_with_a_float_data_with_decimals_not_allowed(ProductValueInterface $value, AttributeInterface $simpleAttribute)
    {
        $value->getData()->willReturn('12.0000');
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('decimal');
        $simpleAttribute->isDecimalsAllowed()->willReturn(false);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12']);
    }

    function it_normalizes_a_value_with_a_string_data(ProductValueInterface $value, AttributeInterface $simpleAttribute)
    {
        $value->getData()->willReturn('my data');
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('varchar');
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'my data']);
    }

    function it_normalizes_a_value_with_a_boolean_data(ProductValueInterface $value, AttributeInterface $simpleAttribute)
    {
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('boolean');

        $value->getData()->willReturn(false);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '0']);

        $value->getData()->willReturn(true);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '1']);
    }

    function it_normalizes_a_value_with_a_collection_data(ProductValueInterface $value, AttributeInterface $simpleAttribute, SerializerInterface $serializer)
    {
        $itemOne = new \stdClass();
        $itemTwo = new \stdClass();
        $collection = new ArrayCollection([$itemOne, $itemTwo]);
        $value->getData()->willReturn($collection);
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('prices');

        $serializer->normalize($collection, 'flat', ['field_name' => 'simple'])->shouldBeCalled()->willReturn(['simple' => 'red, blue']);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'red, blue']);
    }

    function it_normalizes_a_value_with_an_array_data(ProductValueInterface $value, AttributeInterface $simpleAttribute, SerializerInterface $serializer)
    {
        $itemOne = new \stdClass();
        $itemTwo = new \stdClass();
        $array = [$itemOne, $itemTwo];
        $value->getData()->willReturn($array);
        $value->getAttribute()->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('prices');

        $serializer->normalize(Argument::any(), 'flat', ['field_name' => 'simple'])->shouldBeCalled()->willReturn(['simple' => 'red, blue']);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'red, blue']);
    }

    function it_normalizes_a_value_with_ordered_options_with_a_option_collection_data(
        ProductValueInterface $value,
        AttributeInterface $multiColorAttribute,
        SerializerInterface $serializer,
        AttributeOptionInterface $redOption,
        AttributeOptionInterface $blueOption,
        ArrayCollection $collection
    ) {
        $collection->toArray()->willReturn([$redOption, $blueOption]);
        $collection->isEmpty()->willReturn(false);
        $value->getData()->willReturn($collection);
        $value->getAttribute()->willReturn($multiColorAttribute);
        $value->getLocale()->willReturn('en_US');
        $multiColorAttribute->getCode()->willReturn('colors');
        $multiColorAttribute->isLocaleSpecific()->willReturn(false);
        $multiColorAttribute->isLocalizable()->willReturn(false);
        $multiColorAttribute->isScopable()->willReturn(false);
        $multiColorAttribute->getBackendType()->willReturn('options');
        $redOption->getSortOrder()->willReturn(10)->shouldBeCalled();
        $blueOption->getSortOrder()->willReturn(11)->shouldBeCalled();

        // phpspec raises this php bug https://bugs.php.net/bug.php?id=50688,
        // warning: usort(): Array was modified by the user comparison function in ProductValueNormalizer.php line 178
        $previousReporting = error_reporting();
        error_reporting(0);
        $serializer->normalize(Argument::type('Doctrine\Common\Collections\ArrayCollection'), 'flat', ['field_name' => 'colors'])
            ->shouldBeCalled()
            ->willReturn(['colors' => 'red, blue']);

        $this->normalize($value, 'flat', [])->shouldReturn(['colors' => 'red, blue']);
        error_reporting($previousReporting);
    }
}
