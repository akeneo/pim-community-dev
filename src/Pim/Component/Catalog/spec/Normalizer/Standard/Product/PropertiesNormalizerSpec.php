<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class PropertiesNormalizerSpec extends ObjectBehavior
{
    function let(CollectionFilterInterface $filter, SerializerInterface $serializer)
    {
        $this->beConstructedWith($filter);

        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PropertiesNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_format(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($product, 'xml')->shouldReturn(false);
        $this->supportsNormalization($product, 'csv')->shouldReturn(false);
    }

    function it_normalizes_the_properties_of_the_product(
        $filter,
        $serializer,
        ProductInterface $product,
        FamilyInterface $family,
        ValueCollection $values,
        \ArrayIterator $iterator
    ) {
        $values->getIterator()->willReturn($iterator);

        $family->getCode()->willReturn('my_family');
        $product->isVariant()->willReturn(false);
        $product->getFamily()->willReturn($family);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);
        $product->getIdentifier()->willReturn('my_code');
        $product->getValues()->willReturn($values);

        $filter->filterCollection($values, 'pim.transform.product_value.structured', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($values);

        $context = ['filter_types' => ['pim.transform.product_value.structured']];

        $serializer
            ->normalize($values, 'standard', $context)
            ->willReturn(['name' => [['locale' => null, 'scope' => null, 'value' => 'foo']]]);

        $created = new \DateTime('2010-06-23');
        $product->getCreated()->willReturn($created);
        $serializer->normalize($created, 'standard')->willReturn('2010-06-23T00:00:00+01:00');

        $updated = new \DateTime('2010-06-23 23:00:00');
        $product->getUpdated()->willReturn($updated);
        $serializer->normalize($updated, 'standard')->willReturn('2010-06-23T23:00:00+01:00');

        $this->normalize($product, 'standard', $context)->shouldReturn([
            'identifier'    => 'my_code',
            'family'        => 'my_family',
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'name' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'value'  => 'foo',
                    ]
                ]
            ],
            'created'       => '2010-06-23T00:00:00+01:00',
            'updated'       => '2010-06-23T23:00:00+01:00',
        ]);
    }

    function it_normalizes_the_properties_of_the_variant_product(
        $filter,
        $serializer,
        VariantProductInterface $product,
        ProductModel $productModel,
        FamilyInterface $family,
        ValueCollection $values,
        \ArrayIterator $iterator
    ) {
        $values->getIterator()->willReturn($iterator);

        $family->getCode()->willReturn('my_family');
        $product->isVariant()->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->isEnabled()->willReturn(true);
        $product->getIdentifier()->willReturn('my_code');
        $product->getValues()->willReturn($values);
        $product->getParent()->willReturn($productModel);
        $productModel->getCode()->willReturn('parent_code');

        $filter->filterCollection($values, 'pim.transform.product_value.structured', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($values);

        $context = ['filter_types' => ['pim.transform.product_value.structured']];

        $serializer
            ->normalize($values, 'standard', $context)
            ->willReturn(['name' => [['locale' => null, 'scope' => null, 'value' => 'foo']]]);

        $created = new \DateTime('2010-06-23');
        $product->getCreated()->willReturn($created);
        $serializer->normalize($created, 'standard')->willReturn('2010-06-23T00:00:00+01:00');

        $updated = new \DateTime('2010-06-23 23:00:00');
        $product->getUpdated()->willReturn($updated);
        $serializer->normalize($updated, 'standard')->willReturn('2010-06-23T23:00:00+01:00');

        $this->normalize($product, 'standard', $context)->shouldReturn([
            'identifier'    => 'my_code',
            'family'        => 'my_family',
            'parent'        => 'parent_code',
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'name' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'value'  => 'foo',
                    ]
                ]
            ],
            'created'       => '2010-06-23T00:00:00+01:00',
            'updated'       => '2010-06-23T23:00:00+01:00',
        ]);
    }
}
