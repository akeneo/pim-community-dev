<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PropertiesNormalizerSpec extends ObjectBehavior
{
    function let(CollectionFilterInterface $filter, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($filter, $normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PropertiesNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
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
        $normalizer,
        ProductInterface $product,
        FamilyInterface $family,
        WriteValueCollection $values,
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

        $normalizer
            ->normalize($values, 'standard', $context)
            ->willReturn(['name' => [['locale' => null, 'scope' => null, 'value' => 'foo']]]);

        $created = new \DateTime('2010-06-23');
        $product->getCreated()->willReturn($created);
        $normalizer->normalize($created, 'standard')->willReturn('2010-06-23T00:00:00+01:00');

        $updated = new \DateTime('2010-06-23 23:00:00');
        $product->getUpdated()->willReturn($updated);
        $normalizer->normalize($updated, 'standard')->willReturn('2010-06-23T23:00:00+01:00');

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
        $normalizer,
        ProductInterface $product,
        ProductModel $productModel,
        FamilyInterface $family,
        WriteValueCollection $values,
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

        $normalizer
            ->normalize($values, 'standard', $context)
            ->willReturn(['name' => [['locale' => null, 'scope' => null, 'value' => 'foo']]]);

        $created = new \DateTime('2010-06-23');
        $product->getCreated()->willReturn($created);
        $normalizer->normalize($created, 'standard')->willReturn('2010-06-23T00:00:00+01:00');

        $updated = new \DateTime('2010-06-23 23:00:00');
        $product->getUpdated()->willReturn($updated);
        $normalizer->normalize($updated, 'standard')->willReturn('2010-06-23T23:00:00+01:00');

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
