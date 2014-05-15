<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\TransformBundle\Filter\FilterInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, FilterInterface $filter)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
        $this->setFilters(array($filter));
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

    function it_normalizes_the_properties_of_product(
        Product $product,
        ArrayCollection $values,
        \ArrayIterator $iterator,
        $filter
    ) {
        $values->getIterator()->willReturn($iterator);

        $product->getAssociations()->willReturn([]);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);
        $product->getValues()->willReturn($values);

        $filter->filter($values, Argument::any())->shouldBeCalled()->willReturn($values);

        $this->normalize($product, 'csv')->shouldReturn([
            'family' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'associations' => [],
            'values' => []
        ]);
    }

    function it_normalizes_the_values_of_product(
        Product $product,
        AbstractAttribute $attribute,
        ProductValueInterface $value,
        ArrayCollection $values,
        \ArrayIterator $iterator,
        $filter,
        $serializer
    ) {
        $values->getIterator()->willReturn($iterator);

        $product->getAssociations()->willReturn([]);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $product->getValues()->willReturn($values);

        $filter->filter($values, Argument::any())->shouldBeCalled()->willReturn($values);

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
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'value' => 'foo'
                    ]
                ]
            ]
        ]);
    }
}
