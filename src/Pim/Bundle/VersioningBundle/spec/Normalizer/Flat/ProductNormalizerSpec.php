<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductValueNormalizer;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Standard\ProductNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        ProductNormalizer $productNormalizerStandard,
        ProductValueNormalizer $productValueNormalizerFlat
    ) {
        $this->beConstructedWith($productNormalizerStandard, $productValueNormalizerFlat);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization_of_product(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'flat')->shouldBe(true);
        $this->supportsNormalization($product, 'csv')->shouldBe(false);
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_scopable_and_localizable_product(
        ProductNormalizer $productNormalizerStandard,
        ProductValueNormalizer $productValueNormalizerFlat,
        ProductInterface $product
    ) {
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
                'values'        => [
                    'sku'         => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '12',
                        ],
                    ],
                    'description' => [
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => 'VNeck t-shirt',
                        ],
                    ],
                    'price'       => [
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => '10.00',
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                ],
                'associations'  => [],
            ]
        );

        $productValueNormalizerFlat->normalize(
            [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '12',
                    ],
                ],
            ],
            'flat',
            Argument::cetera()
        )->willReturn(
            [
                'sku' => '12',
            ]
        );

        $productValueNormalizerFlat->normalize(
            [
                'description' => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => 'VNeck t-shirt',
                    ],
                ],
            ],
            'flat',
            Argument::cetera()
        )->willReturn(
            [
                'description-fr_FR' => 'VNeck t-shirt',
            ]
        );

        $productValueNormalizerFlat->normalize(
            [
                'price' => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => [
                            [
                                'amount'   => '10.00',
                                'currency' => 'EUR',
                            ],
                        ],
                    ],
                ],

            ],
            'flat',
            Argument::cetera()
        )->willReturn(
            [
                'price-fr_FR'      => '10.00',
                'price-unit-fr_FR' => 'EUR',
            ]
        );

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'            => 'familyA',
                'groups'            => 'groupA,groupB',
                'variant_group'     => 'variantA',
                'categories'        => 'categoryA,categoryB',
                'enabled'           => false,
                'description-fr_FR' => 'VNeck t-shirt',
                'price-fr_FR'       => '10.00',
                'price-unit-fr_FR'  => 'EUR',
                'sku'               => '12',
            ]
        );
    }

    function it_normalizes_product_with_associations(
        ProductNormalizer $productNormalizerStandard,
        ProductValueNormalizer $productValueNormalizerFlat,
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
                'values'        => [
                    'sku' => [
                        'scope'  => null,
                        'locale' => null,
                        'data'   => 'sku-001',
                    ],
                ],
                'associations'  => [
                    'cross_sell' => [
                        'products' => [],
                        'groups'   => [],
                    ],
                    'up_sell'    => [
                        'groups' => ['associated_group1', 'associated_group2'],
                        'products'   => ['sku_assoc_product1', 'sku_assoc_product2'],
                    ],
                ],
            ]
        );

        $productValueNormalizerFlat->normalize(
            [
                'sku' => [
                    'scope'  => null,
                    'locale' => null,
                    'data'   => 'sku-001',
                ],
            ],
            'flat',
            Argument::cetera()
        )->willReturn(
            [
                'sku' => 'sku-001',
            ]
        );

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'              => 'shoes',
                'groups'              => 'group1,group2,variant_group_1',
                'variant_group'       => 'variantA',
                'categories'          => 'categoryA,categoryB',
                'enabled'             => false,
                'cross_sell-groups'   => '',
                'cross_sell-products' => '',
                'up_sell-groups'      => 'associated_group1,associated_group2',
                'up_sell-products'    => 'sku_assoc_product1,sku_assoc_product2',
                'sku'                 => 'sku-001',
            ]
        );
    }
}
