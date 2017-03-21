<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\PropertiesNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class PropertiesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PropertiesNormalizer::class);
    }

    function it_support_products(ProductInterface $product)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($product, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($product, 'indexing')->shouldReturn(true);
    }

    function it_normalizes_product_properties_with_empty_fields_and_values(
        ProductInterface $product,
        ProductValueCollectionInterface $productValueCollection
    ) {
        $product->getIdentifier()->willReturn('sku-001');

        $product->getValues()->willReturn($productValueCollection);
        $product->getFamily()->willReturn(null);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $productValueCollection->isEmpty()->willReturn(true);

        $this->normalize($product, 'indexing')->shouldReturn(
            [
                'identifier' => 'sku-001',
                'family'     => null,
                'categories' => [],
                'groups'     => [],
                'values'     => [],
            ]
        );
    }

    /*
     * // TODO: TIP-706- To re-enable once productValueCollectionNormalizer is working with a
     * // TODO: TIP-706- product value normalizer
    function it_normalizes_product_fields_and_values(
        $serializer,
        ProductInterface $product,
        ProductValueCollectionInterface $productValueCollection,
        FamilyInterface $family
    ) {
        $product->getIdentifier()->willReturn('sku-001');

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('a_family');
        $product->getGroupCodes()->willReturn(['first_group', 'second_group']);

        $product->getValues()
            ->shouldBeCalledTimes(2)
            ->willReturn($productValueCollection);
        $productValueCollection->isEmpty()->willReturn(false);

        $product->getCategoryCodes()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );

        $serializer->normalize($productValueCollection, 'indexing', [])
            ->willReturn(
                [
                    'a_size-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => '10.51',
                        ],
                    ],
                ]
            );

        $this->normalize($product, 'indexing')->shouldReturn(
            [
                'identifier' => 'sku-001',
                'family'     => 'a_family',
                'categories' => ['first_category', 'second_category'],
                'groups'     => ['first_group', 'second_group'],
                'values'     => [
                    'a_size-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => '10.51',
                        ],
                    ],
                ],
            ]
        );
    }
    */
}
