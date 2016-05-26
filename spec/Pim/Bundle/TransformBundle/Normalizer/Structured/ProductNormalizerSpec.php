<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Structured;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Serializer;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(CollectionFilterInterface $filter, Serializer $serializer)
    {
        $this->beConstructedWith($filter);

        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Structured\ProductNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_is_serializer_aware()
    {
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_normalization_of_products_in_json_and_xml(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'json')->shouldReturn(true);
        $this->supportsNormalization($product, 'xml')->shouldReturn(true);
        $this->supportsNormalization($product, 'csv')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_other_entities(AttributeInterface $attribute)
    {
        $this->supportsNormalization($attribute, 'json')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'xml')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'json')->shouldReturn(false);
    }

    function it_normalizes_the_properties_of_product(
        $filter,
        ProductInterface $product,
        ArrayCollection $values,
        \ArrayIterator $iterator
    ) {
        $filter
            ->filterCollection(
                $values,
                'pim.transform.product_value.structured',
                [
                    "only_associations"    => false,
                    "exclude_associations" => false,
                    "entity"               => "product",
                    'scopeCode'            => 'scope',
                    'localeCodes'          => ['locale'],
                    'channels'             => ['scope'],
                    'locales'              => ['locale'],
                ]
            )
            ->shouldBeCalled()
            ->willReturn($values);

        $product->getValues()->willReturn($values);

        $values->getIterator()->willReturn($iterator);
        $iterator->rewind()->willReturn(null);
        $iterator->valid()->will(function () use (&$valueCount) {
            return false;
        });
        $iterator->next()->willReturn(null);

        $product->getAssociations()->willReturn([]);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getVariantGroup()->willReturn(null);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);

        $this->normalize($product, 'json', [
            'scopeCode'   => 'scope',
            'localeCodes' => ['locale']
        ])->shouldReturn([
            'family' => null,
            'groups' => [],
            'variant_group' => null,
            'categories' => [],
            'enabled' => true,
            'values' => [],
            'associations' => [],
        ]);
    }

    function it_normalizes_the_values_of_product(
        ProductInterface $product,
        AttributeInterface $attribute,
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
        $product->getVariantGroup()->willReturn(null);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $product->getValues()->willReturn($values);

        $filter
            ->filterCollection(
                $values,
                'pim.transform.product_value.structured',
                [
                    "only_associations"    => false,
                    "exclude_associations" => false,
                    "entity"               => "product",
                    'scopeCode'            => 'scope',
                    'localeCodes'          => ['locale'],
                    'channels'             => ['scope'],
                    'locales'              => ['locale'],
                ]
            )
            ->shouldBeCalled()
            ->willReturn($values);

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

        $this->normalize($product, 'json', [
            'scopeCode'   => 'scope',
            'localeCodes' => ['locale']
        ])->shouldReturn([
            'family' => null,
            'groups' => [],
            'variant_group' => null,
            'categories' => [],
            'enabled' => true,
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'value' => 'foo',
                    ]
                ]
            ],
            'associations' => [],
        ]);
    }
}
