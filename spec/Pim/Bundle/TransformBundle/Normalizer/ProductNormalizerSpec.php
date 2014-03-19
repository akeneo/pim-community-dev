<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_is_serializer_aware()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_normalization_of_products_in_json_and_xml(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'json')->shouldReturn(true);
        $this->supportsNormalization($product, 'xml')->shouldReturn(true);
        $this->supportsNormalization($product, 'csv')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_other_entities(AbstractAttribute $attribute)
    {
        $this->supportsNormalization($attribute, 'json')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'xml')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'json')->shouldReturn(false);
    }

    function it_normalizes_the_properties_of_product(Product $product)
    {
        $product->getAssociations()->willReturn([]);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);
        $product->getValues()->willReturn(new ArrayCollection());

        $this->normalize($product, 'csv')->shouldReturn([
            'family' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'associations' => []
        ]);
    }

    function it_normalizes_the_values_of_product(Product $product, AbstractAttribute $attribute, ProductValue $value, ArrayCollection $collection, \ArrayIterator $iterator, $serializer)
    {
        $product->getAssociations()->willReturn([]);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $product->getValues()->willReturn($collection);

        $collection->filter(Argument::cetera())->willReturn($collection);
        $collection->getIterator()->willReturn($iterator);

        $iterator->rewind()->willReturn(null);
        $valueCount = 1;
        $iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $iterator->current()->willReturn($value);
        $iterator->next()->willReturn(null);

        $serializer
            ->normalize($value, 'json', Argument::any())
            ->willReturn(['locale' => null, 'scope' => null, 'value' => 'foo']);

        $this->normalize($product, 'json')->shouldReturn([
            'family' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'associations' => [],
            'name' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'value' => 'foo'
                ]
            ]
        ]);
    }
}
