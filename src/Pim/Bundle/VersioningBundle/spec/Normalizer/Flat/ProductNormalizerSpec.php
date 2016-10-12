<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Standard\ProductNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(ProductNormalizer $productNormalizerStandard)
    {
        $this->beConstructedWith($productNormalizerStandard);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_csv_normalization_of_product(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'flat')->shouldBe(true);
        $this->supportsNormalization($product, 'csv')->shouldBe(false);
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_product(
        ProductNormalizer $productNormalizerStandard,
        ProductInterface $product
    ) {
        $productNormalizerStandard->supportsNormalization($product, 'standard')->willReturn(true);
        $productNormalizerStandard->normalize($product, 'standard', [])->willReturn(
            [
                'identifier'    => 'sku-001',
                'family'        => 'familyA',
                'groups'        => ['groupA', 'groupB'],
                'variant_group' => 'variantA',
                'categories'    => ['categoryA', 'categoryB'],
                'enabled'       => false,
                'created'       => '2016-06-23T11:24:44+02:00',
                'updated'       => '2016-06-23T11:24:44+02:00',
            ]
        );

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'identifier' => 'sku-001',
                'family'     => 'familyA',
                'groups'     => 'groupA,groupB',
                'categories' => 'categoryA,categoryB',
                'enabled'    => true,
            ]
        );
    }

    function it_normalizes_product_with_associations(
        ProductNormalizer $productNormalizerStandard,
        ProductInterface $product
    ) {
        $productNormalizerStandard->supportsNormalization($product, 'standard')->willReturn(true);
        $productNormalizerStandard->normalize($product, 'standard', [])->willReturn(
            [
                'identifier'    => 'sku-001',
                'family'        => 'shoes',
                'groups'        => ['group1', 'group2', 'variant_group_1'],
                'variant_group' => 'variantA',
                'categories'    => ['categoryA', 'categoryB'],
                'enabled'       => false,
                'created'       => '2016-06-23T11:24:44+02:00',
                'updated'       => '2016-06-23T11:24:44+02:00',
                'associations' => [
                    'cross_sell' => [
                        'products' => [],
                        'groups' => []
                    ],
                    'up_sell' => [
                        'products' => ['associated_group1', 'associated_group2'],
                        'groups' => ['sku_assoc_product1', 'sku_assoc_product2']
                    ]
                ]
            ]
        );

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'sku'                 => 'sku-001',
                'family'              => 'shoes',
                'groups'              => 'group1,group2,variant_group_1',
                'categories'          => 'nice shoes,converse',
                'cross_sell-groups'   => '',
                'cross_sell-products' => '',
                'up_sell-groups'      => 'associated_group1,associated_group2',
                'up_sell-products'    => 'sku_assoc_product1,sku_assoc_product2',
                'enabled'             => true,
            ]
        );
    }

    function it_normalizes_product_with_a_multiselect_value(
        ProductNormalizer $productNormalizerStandard,
        ProductInterface $product
    ) {
        $productNormalizerStandard->supportsNormalization($product, 'standard')->willReturn(true);
        $productNormalizerStandard->normalize($product, 'standard', [])->willReturn(
            [
                'identifier'    => 'sku-001',
                'family'        => 'shoes',
                'groups'        => [],
                'variant_group' => 'variantA',
                'categories'    => [],
                'enabled'       => false,
                'created'       => '2016-06-23T11:24:44+02:00',
                'updated'       => '2016-06-23T11:24:44+02:00',
                'associations'  => [],
                'values'        => [
                    'colors' => [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['red', 'blue'],
                    ],
                ]
            ]
        );

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'sku'        => 'sku-001',
                'family'     => 'shoes',
                'groups'     => '',
                'categories' => '',
                'colors'     => 'red, blue',
                'enabled'    => 1,
            ]
        );
    }

    function it_normalizes_product_with_price(
        ProductNormalizer $productNormalizerStandard,
        ProductInterface $product
    ) {
        $productNormalizerStandard->supportsNormalization($product, 'standard')->willReturn(true);
        $productNormalizerStandard->normalize($product, 'standard', [])->willReturn(
            [
                'identifier'    => 'sku-001',
                'family'        => 'shoes',
                'groups'        => [],
                'variant_group' => 'variantA',
                'categories'    => [],
                'enabled'       => false,
                'created'       => '2016-06-23T11:24:44+02:00',
                'updated'       => '2016-06-23T11:24:44+02:00',
                'associations'  => [],
                'values'        => [
                    'price' => [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            [
                                'amount'   => '356.00',
                                'currency' => 'EUR',
                            ],
                        ],
                    ],
                ]
            ]
        );
        $this->normalize($product, 'flat', ['price-EUR' => ''])->shouldReturn(
            [
                'price-EUR'  => '356.00',
                'family'     => 'shoes',
                'groups'     => 'group1,group2,variant_group_1',
                'categories' => 'nice shoes,converse',
                'enabled'    => 1,
            ]
        );
    }
}
