<?php

namespace spec\Pim\Component\Catalog\ProductModel\Filter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
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
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($productModelRepository, $familyRepository, $productRepository, $attributeRepository);
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
        $attributeRepository,
        FamilyInterface $family,
        ProductInterface $product,
        Collection $familyAttributes,
        Collection $familyAttributeCodes,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);

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
        $attributeRepository,
        ProductModelInterface $productModel,
        ProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $sku,
        AttributeInterface $weight,
        AttributeInterface $description,
        AttributeInterface $color,
        AttributeInterface $name
    ) {
        $attributeRepository->findOneByIdentifier('sku')->willReturn($sku);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $attributeRepository->findOneByIdentifier('description')->willReturn($description);
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weight);

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

    function it_keeps_attributes_and_axes_coming_from_family_variant(
        $productRepository,
        $productModelRepository,
        $attributeRepository,
        ProductModelInterface $productModel,
        ProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attributes,
        Collection $axes,
        AttributeInterface $sku,
        AttributeInterface $euShoesSize,
        AttributeInterface $weight
    ) {
        $attributeRepository->findOneByIdentifier('sku')->willReturn($sku);
        $attributeRepository->findOneByIdentifier('eu_shoes_size')->willReturn($euShoesSize);
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weight);

        $productRepository->findOneByIdentifier('shoes')->willReturn($product);
        $productModelRepository->findOneByIdentifier('brooksblue')->willReturn($productModel);

        $productModel->getFamilyVariant()->willreturn($familyVariant);
        $productModel->getVariationLevel()->willreturn(1);

        $familyVariant->getVariantAttributeSet(2)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attributes);
        $variantAttributeSet->getAxes()->willReturn($axes);
        $attributes->toArray()->willReturn([$sku, $euShoesSize]);

        $axes->toArray()->willReturn([$weight]);

        $sku->getCode()->willReturn('sku');
        $weight->getCode()->willReturn('weight');
        $euShoesSize->getCode()->willReturn('eu_shoes_size');

        $this->filter([
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'shoes',
                    ],
                ],
                'eu_shoes_size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => '43',
                    ]
                ],
                'weight' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            'amount' => '600.0000',
                            'unit'   => 'GRAM'
                        ]
                    ]
                ]
            ]
        ])->shouldReturn([
            'identifier' => 'shoes',
            'parent' => 'brooksblue',
            'family' => 'shoes',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'shoes',
                    ],
                ],
                'eu_shoes_size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => '43',
                    ]
                ],
                'weight' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            'amount' => '600.0000',
                            'unit'   => 'GRAM'
                        ]
                    ]
                ]
            ],
        ]);
    }

    function it_throws_an_exception_when_an_attribute_does_not_exists() {
        $this->shouldThrow(
            UnknownPropertyException::class
        )->during(
            'filter',
            [
                [
                    'identifier' => 'tshirt',
                    'values'     => [
                        'sku' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'tshirt',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
