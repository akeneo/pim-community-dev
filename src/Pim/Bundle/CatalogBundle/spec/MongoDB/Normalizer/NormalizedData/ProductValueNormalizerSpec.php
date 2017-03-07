<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductPrice;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_value(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('foo');

        $this->supportsNormalization($value, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($value, 'json')->shouldBe(false);
        $this->supportsNormalization($value, 'xml')->shouldBe(false);
    }

    function it_normalizes_value_with_simple_data(
        SerializerInterface $serializer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);

        $attribute->getCode()->willReturn('code');
        $attribute->getBackendType()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $data = 'product title';
        $value->getData()->willReturn($data);
        $value->getAttribute()->willReturn($attribute);

        $serializer->normalize($data, 'mongodb_json', [])->willReturn($data);

        $this->normalize($value, 'mongodb_json', [])->shouldReturn(['code' => 'product title']);
    }

    function it_normalizes_value_with_collection_data(
        SerializerInterface $serializer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);

        $attribute->getCode()->willReturn('code');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $price = new ProductPrice(42, 'EUR');
        $collection = new ArrayCollection([$price]);

        $value->getData()->willReturn($collection);
        $value->getAttribute()->willReturn($attribute);
        $serializer->normalize($price, 'mongodb_json', [])->willReturn(['amount' => 42, 'currency' => 'EUR']);

        $this
            ->normalize($value, 'mongodb_json', [])
            ->shouldReturn(['code' => ['EUR' => ['amount' => 42, 'currency' => 'EUR']]]);
    }

    function it_normalizes_value_with_empty_collection_data(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        Collection $collection,
        \Iterator $iterator
    ) {
        $attribute->getCode()->willReturn('code');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $collection->getIterator()->willReturn($iterator);
        $value->getData()->willReturn($collection);
        $value->getAttribute()->willReturn($attribute);

        $this->normalize($value, 'mongodb_json', [])->shouldReturn(null);
    }

    function it_normalizes_value_with_decimal_support_backend(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('code');
        $attribute->getBackendType()->willReturn('decimal');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $value->getData()->willReturn('42.42');
        $value->getAttribute()->willReturn($attribute);

        $this->normalize($value, 'mongodb_json', [])->shouldReturn(['code' => 42.42]);
    }
}
