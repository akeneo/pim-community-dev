<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, AbstractAttribute $simpleAttribute)
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

    function it_supports_csv_normalization_of_product_value(AbstractProductValue $value)
    {
        $this->supportsNormalization($value, 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_product(AbstractProductValue $value)
    {
        $this->supportsNormalization($value, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_value_with_null_data(AbstractProductValue $value, $simpleAttribute)
    {
        $value->getData()->willReturn(null);
        $value->getAttribute()->willReturn($simpleAttribute);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '']);
    }

    function it_normalizes_a_value_with_a_integer_data(AbstractProductValue $value, $simpleAttribute)
    {
        $value->getData()->willReturn(12);
        $value->getAttribute()->willReturn($simpleAttribute);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12']);
    }

    function it_normalizes_a_value_with_a_float_data(AbstractProductValue $value, $simpleAttribute)
    {
        $value->getData()->willReturn(12.25);
        $value->getAttribute()->willReturn($simpleAttribute);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12.2500']);
    }

    function it_normalizes_a_value_with_a_string_data(AbstractProductValue $value, $simpleAttribute)
    {
        $value->getData()->willReturn('my data');
        $value->getAttribute()->willReturn($simpleAttribute);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'my data']);
    }

    function it_normalizes_a_value_with_a_boolean_data(AbstractProductValue $value, $simpleAttribute)
    {
        $value->getData()->willReturn(false);
        $value->getAttribute()->willReturn($simpleAttribute);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '0']);

        $value->getData()->willReturn(true);
        $value->getAttribute()->willReturn($simpleAttribute);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '1']);
    }

    function it_normalizes_a_value_with_a_collection_data(AbstractProductValue $value, $simpleAttribute, $serializer)
    {
        $itemOne = new \stdClass();
        $itemTwo = new \stdClass();
        $collection = new ArrayCollection([$itemOne, $itemTwo]);
        $value->getData()->willReturn($collection);
        $value->getAttribute()->willReturn($simpleAttribute);

        $serializer
            ->normalize($collection, 'flat', ['field_name' => 'simple'])
            ->shouldBeCalled()
            ->willReturn(['simple' => 'red, blue']);

        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'red, blue']);
    }

    function it_normalizes_a_value_with_an_array_data(AbstractProductValue $value, $simpleAttribute, $serializer)
    {
        $itemOne = new \stdClass();
        $itemTwo = new \stdClass();
        $array = [$itemOne, $itemTwo];
        $value->getData()->willReturn($array);
        $value->getAttribute()->willReturn($simpleAttribute);

        $serializer
            ->normalize(Argument::any(), 'flat', ['field_name' => 'simple'])
            ->shouldBeCalled()
            ->willReturn(['simple' => 'red, blue']);

        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'red, blue']);
    }
}
