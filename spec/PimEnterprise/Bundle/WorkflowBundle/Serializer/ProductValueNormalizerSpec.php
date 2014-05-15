<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\CatalogBundle\Model;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function it_is_a_serializer_aware_normalizer_and_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_normalization_of_product_value_in_the_proposal_format(Model\AbstractProductValue $value)
    {
        $this->supportsNormalization($value, 'proposal')->shouldBe(true);
    }

    function it_normalizes_product_value_using_its_scalar__data(Model\AbstractProductValue $value)
    {
        $value->getData()->willReturn('foo');
        $this->normalize($value, 'proposal')->shouldReturn('foo');
    }

    function it_delegates_normalization_of_product_value_non_scalar_data(
        Model\AbstractProductValue $value,
        Foo $data,
        SerializerInterface $serializer
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $value->getData()->willReturn($data);

        $serializer->normalize($data, 'proposal', [])->willReturn('fooObject');

        $this->setSerializer($serializer);
        $this->normalize($value, 'proposal')->shouldReturn('fooObject');
    }

    function it_supports_denormalization_of_value_from_the_proposal_format()
    {
        $this->supportsDenormalization([], 'value', 'proposal')->shouldBe(true);
    }

    function it_denormalizes_data_into_scalar_attribute_value(Model\AbstractProductValue $value)
    {
        $value->setData('foo')->willReturn($value);

        $this->denormalize('foo', 'pim_catalog_text', 'proposal', ['instance' => $value])->shouldReturn($value);
    }

    function it_delegates_denormalization_of_non_scalar_attribute_value(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute,
        Foo $data,
        SerializerInterface $serializer
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');

        $value->getData()->willReturn($data);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('custom_type');
        $serializer->denormalize(['a' => 'b'], 'custom_type', 'proposal', ['instance' => $data])->willReturn($data);
        $value->setData($data)->willReturn($value);

        $this->setSerializer($serializer);
        $this->denormalize(['a' => 'b'], 'value', 'proposal', ['instance' => $value])->shouldReturn($value);
    }
}

class Foo {}
