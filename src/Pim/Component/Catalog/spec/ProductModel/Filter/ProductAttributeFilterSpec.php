<?php

namespace spec\Pim\Component\Catalog\ProductModel\Filter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Prophecy\Argument;

class ProductAttributeFilterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $familyRepository,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($productModelRepository, $familyRepository, $productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Pim\Component\Catalog\ProductModel\Filter\ProductAttributeFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(\Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface::class);
    }

    function it_filters_the_attributes_that_does_not_belong_the_family(
        $familyRepository,
        $productRepository,
        FamilyInterface $family,
        ProductInterface $product,
        Collection $familyAttributes,
        Collection $familyAttributeCodes
    ) {
        $familyRepository->findOneByIdentifier('Summer Tshirt')->willReturn($family);
        $family->getAttributes()->willReturn($familyAttributes);
        $familyAttributes->map(Argument::any())->willReturn($familyAttributeCodes);
        $familyAttributeCodes->toArray()->willReturn(['sku', 'description']);

        $productRepository->findOneByIdentifier('tshirt')->willReturn($product);

        $expected = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt',
                    ],
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'mobile',
                        'data' => 'My awesome description',
                    ],
                ],
            ],
        ];

        $this->filter(
            [
                'identifier' => 'tshirt',
                'family' => 'Summer Tshirt',
                'values' => [
                    'sku' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'tshirt',
                        ],
                    ],
                    'name' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => 'My very awesome T-shirt',
                        ],
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'mobile',
                            'data' => 'My awesome description',
                        ],
                    ],
                ],
            ]
        )->shouldReturn($expected);
    }

    function it_filters_the_attributes_that_does_not_belong_to_a_family_variant(
        $productModelRepository,
        $productRepository,
        ProductModelInterface $productModel,
        ProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $sku,
        AttributeInterface $weight,
        AttributeInterface $description,
        AttributeInterface $color
    ) {
        $productModelRepository->findOneByIdentifier('parent-code')->willReturn($productModel);
        $productModel->getFamilyVariant()->willreturn($familyVariant);
        $productModel->getVariationLevel()->willreturn(1);

        $productRepository->findOneByIdentifier('tshirt')->willReturn($product);

        $familyVariant->getVariantAttributeSet(2)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attributes);
        $variantAttributeSet->getAxes()->willReturn($axes);
        $attributes->toArray()->willReturn([$sku, $weight, $description]);
        $axes->toArray()->willReturn([$color]);

        $sku->getCode()->willReturn('sku');
        $weight->getCode()->willReturn('weight');
        $description->getCode()->willReturn('description');
        $color->getCode()->willReturn('color');

        $expected = [
            'identifier' => 'tshirt',
            'parent' => 'parent-code',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt',
                    ],
                ],
                'weight' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'mobile',
                        'data' => [
                            'unit' => 'GRAM',
                            'amount' => '30'
                        ]
                    ],
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'mobile',
                        'data' => 'My awesome description',
                    ],
                ],
                'color' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'mobile',
                        'data' => '[blue]',
                    ],
                ],
            ],
        ];

        $this->filter(
            [
                'identifier' => 'tshirt',
                'parent' => 'parent-code',
                'values' => [
                    'sku' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'tshirt',
                        ],
                    ],
                    'name' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => 'My very awesome T-shirt',
                        ],
                    ],
                    'weight' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'mobile',
                            'data' => [
                                'unit' => 'GRAM',
                                'amount' => '30'
                            ]
                        ],
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'mobile',
                            'data' => 'My awesome description',
                        ],
                    ],
                    'color' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'mobile',
                            'data' => '[blue]',
                        ],
                    ],
                ],
            ]
        )->shouldReturn($expected);
    }
}
