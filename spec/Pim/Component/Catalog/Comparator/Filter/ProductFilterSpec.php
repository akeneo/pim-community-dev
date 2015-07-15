<?php

namespace spec\Pim\Component\Catalog\Comparator\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Comparator\ComparatorInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductFilterSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($normalizer, $comparatorRegistry, $attributeRepository, ['family', 'enabled']);
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface');
    }

    function it_returns_all_values_on_a_new_product(
        $normalizer,
        $comparatorRegistry,
        $attributeRepository,
        ProductInterface $product,
        ComparatorInterface $familyComparator,
        ComparatorInterface $descriptionComparator
    ) {
        $originalValues = [];
        $newValues = [
            'family' => 'tshirt',
            'description'   => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'value'  => 'Ma description'
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'value'  => 'My description'
                ]
            ]
        ];

        $attributeRepository->getAttributeTypeByCodes(array_keys($newValues))->willReturn([
            'description' => 'pim_catalog_textarea'
        ]);

        $normalizer->normalize($product, 'json', ['exclude_associations' => true])
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('family')->willReturn($familyComparator);
        $familyComparator->compare($newValues['family'], null)->willReturn($newValues['family']);

        $comparatorRegistry->getAttributeComparator('pim_catalog_textarea')->willReturn($descriptionComparator);
        $descriptionComparator->compare($newValues['description'][0], [])->willReturn($newValues['description'][0]);
        $descriptionComparator->compare($newValues['description'][1], [])->willReturn($newValues['description'][1]);

        $this->filter($product, $newValues)->shouldReturn($newValues);
    }

    function it_filters_not_updated_values(
        $normalizer,
        $comparatorRegistry,
        $attributeRepository,
        ProductInterface $product,
        ComparatorInterface $familyComparator,
        ComparatorInterface $descriptionComparator
    ) {
        $originalValues = [
            'family' => 'tshirt',
            'values' => [
                'description'   => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'value'  => 'Ma description'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'value'  => 'Ma description'
                    ]
                ]
            ]
        ];
        $newValues = [
            'family' => 'tshirt',
            'description'   => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'value'  => 'Ma description'
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'value'  => 'My description'
                ]
            ]
        ];

        $attributeRepository->getAttributeTypeByCodes(array_keys($newValues))->willReturn([
            'description' => 'pim_catalog_textarea'
        ]);

        $normalizer->normalize($product, 'json', ['exclude_associations' => true])
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('family')->willReturn($familyComparator);
        $familyComparator->compare($newValues['family'], $originalValues['family'])->willReturn(null);

        $comparatorRegistry->getAttributeComparator('pim_catalog_textarea')->willReturn($descriptionComparator);
        $descriptionComparator->compare($newValues['description'][0], $originalValues['values']['description'][0])
            ->willReturn(null);
        $descriptionComparator->compare($newValues['description'][1], $originalValues['values']['description'][1])
            ->willReturn($newValues['description'][1]);

        $this->filter($product, $newValues)->shouldReturn([
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'value'  => 'My description'
                ]
            ]
        ]);
    }

    function it_returns_null_when_new_and_original_products_are_equals(
        $normalizer,
        $comparatorRegistry,
        $attributeRepository,
        ProductInterface $product,
        ComparatorInterface $familyComparator,
        ComparatorInterface $descriptionComparator
    ) {
        $originalValues = [
            'family' => 'tshirt',
            'values' => [
                'description'   => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'value'  => 'Ma description'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'value'  => 'My description'
                    ]
                ]
            ]
        ];
        $newValues = [
            'family' => 'tshirt',
                'description'   => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'value'  => 'Ma description'
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'value'  => 'My description'
                ]
            ]
        ];

        $attributeRepository->getAttributeTypeByCodes(array_keys($newValues))->willReturn([
            'description' => 'pim_catalog_textarea'
        ]);

        $normalizer->normalize($product, 'json', ['exclude_associations' => true])
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('family')->willReturn($familyComparator);
        $familyComparator->compare($newValues['family'], $originalValues['family'])->willReturn(null);

        $comparatorRegistry->getAttributeComparator('pim_catalog_textarea')->willReturn($descriptionComparator);
        $descriptionComparator->compare($newValues['description'][0], $originalValues['values']['description'][0])
            ->willReturn(null);
        $descriptionComparator->compare($newValues['description'][1], $originalValues['values']['description'][1])
            ->willReturn(null);

        $this->filter($product, $newValues)->shouldReturn([]);
    }

    function it_throws_an_exception_if_code_is_not_found(
        $normalizer,
        ProductInterface $product
    ) {
        $originalValues = $newValues = [
            'categories' => []
        ];

        $normalizer->normalize($product, 'json', ['exclude_associations' => true])
            ->willReturn($originalValues);

        $this
            ->shouldThrow('LogicException')
            ->during(
                'filter',
                [$product, $newValues]
            );
    }
}
