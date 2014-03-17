<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_value(ProductValue $value)
    {
        $this->supportsNormalization($value, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($value, 'json')->shouldBe(false);
        $this->supportsNormalization($value, 'xml')->shouldBe(false);
    }

    function it_normalizes_value_with_simple_data(SerializerInterface $serializer, ProductValue $value, AbstractAttribute $attribute)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);

        $attribute->getCode()->willReturn('code');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $data = 'product title';
        $value->getData()->willReturn($data);
        $value->getAttribute()->willReturn($attribute);

        $serializer->normalize($data, 'mongodb_json', [])->willReturn($data);

        $this->normalize($value, 'mongodb_json', [])->shouldReturn(['code' => 'product title']);
    }

    function it_normalizes_value_with_collection_data(ProductValue $value, AbstractAttribute $attribute, Collection $collection, \Iterator $iterator)
    {

        $attribute->getCode()->willReturn('code');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $collection->getIterator()->willReturn($iterator);
        $value->getData()->willReturn($collection);
        $value->getAttribute()->willReturn($attribute);

        $this->normalize($value, 'mongodb_json', [])->shouldReturn(['code' => []]);
    }
}
