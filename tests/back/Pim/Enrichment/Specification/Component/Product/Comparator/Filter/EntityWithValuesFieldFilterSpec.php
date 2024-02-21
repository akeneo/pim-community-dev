<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityWithValuesFieldFilterSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, ComparatorRegistry $comparatorRegistry)
    {
        $this->beConstructedWith($normalizer, $comparatorRegistry, ['family', 'enabled', 'categories', 'groups', 'associations']);
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf(FilterInterface::class);
    }

    function it_returns_all_fields_a_new_product(
        $normalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $scalarComparator,
        ComparatorInterface $arrayComparator,
        ComparatorInterface $booleanComparator
    ) {
        $originalFields = [];
        $newFields = [
            'enabled' => true,
            'family' => 'tshirt',
            'categories' => ['categoryA', 'categoryB'],
            'groups' => ['groupA', 'groupB'],
            'associations' => [
                'X_SELL' => [
                    'products' => ['productA', 'productB']
                ],
                'UPSELL' => [
                    'products' => ['productC', 'productB'],
                    'groups' => ['groupA', 'groupB']
                ]
            ]
        ];

        $normalizer->normalize($product, 'standard')->willReturn($originalFields);

        $comparatorRegistry->getFieldComparator('enabled')->willReturn($booleanComparator);
        $booleanComparator->compare($newFields['enabled'], null)->willReturn($newFields['enabled']);

        $comparatorRegistry->getFieldComparator('family')->willReturn($scalarComparator);
        $scalarComparator->compare($newFields['family'], null)->willReturn($newFields['family']);

        $comparatorRegistry->getFieldComparator('groups')->willReturn($arrayComparator);
        $arrayComparator->compare($newFields['groups'], null)->willReturn($newFields['groups']);

        $comparatorRegistry->getFieldComparator('categories')->willReturn($arrayComparator);
        $arrayComparator->compare($newFields['categories'], null)->willReturn($newFields['categories']);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare($newFields['associations'], null)->willReturn($newFields['associations']);

        $this->filter($product, $newFields)->shouldReturn($newFields);
    }

    function it_filters_not_updated_values(
        $normalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $scalarComparator,
        ComparatorInterface $arrayComparator,
        ComparatorInterface $booleanComparator
    ) {
        $originalFields = [
            'enabled' => true,
            'family' => 'tshirt',
            'groups' => ['groupA', 'groupB'],
            'associations' => [
                'X_SELL' => [
                    'products' => ['productA', 'productB']
                ],
                'UPSELL' => [
                    'products' => ['productC', 'productB'],
                    'groups' => ['groupA', 'groupB']
                ]
            ]
        ];

        $newFields = [
            'enabled' => false,
            'family' => 'cameras',
            'groups' => ['groupA'],
            'associations' => [
                'X_SELL' => [
                    'products' => ['productA']
                ],
                'UPSELL' => [
                    'products' => ['productB', 'productC'],
                ]
            ]
        ];

        $filteredFields = $newFields;
        unset($filteredFields['associations']['UPSELL']);

        $normalizer->normalize($product, 'standard')->willReturn($originalFields);

        $comparatorRegistry->getFieldComparator('enabled')->willReturn($booleanComparator);
        $booleanComparator->compare($newFields['enabled'], $originalFields['enabled'])->willReturn($filteredFields['enabled']);

        $comparatorRegistry->getFieldComparator('family')->willReturn($scalarComparator);
        $scalarComparator->compare($newFields['family'], $originalFields['family'])->willReturn($filteredFields['family']);

        $comparatorRegistry->getFieldComparator('groups')->willReturn($arrayComparator);
        $arrayComparator->compare($newFields['groups'], $originalFields['groups'])->willReturn($filteredFields['groups']);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare($newFields['associations'], $originalFields['associations'])->willReturn($filteredFields['associations']);

        $this->filter($product, $newFields)->shouldReturn($filteredFields);
    }

    function it_returns_null_when_new_and_original_products_are_equals(
        $normalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $scalarComparator,
        ComparatorInterface $arrayComparator,
        ComparatorInterface $booleanComparator
    ) {
        $originalFields = [
            'enabled' => true,
            'family' => 'tshirt',
            'groups' => ['groupA', 'groupB'],
            'associations' => [
                'X_SELL' => [
                    'products' => ['productA', 'productB']
                ],
                'UPSELL' => [
                    'products' => ['productC', 'productB'],
                    'groups' => ['groupA', 'groupB']
                ]
            ]
        ];

        $newFields = [
            'enabled' => true,
            'family' => 'tshirt',
            'groups' => ['groupB', 'groupA'],
            'associations' => [
                'X_SELL' => [
                    'products' => ['productB', 'productA']
                ],
                'UPSELL' => [
                    'products' => ['productB', 'productC'],
                ]
            ]
        ];

        $normalizer->normalize($product, 'standard')->willReturn($originalFields);

        $comparatorRegistry->getFieldComparator('enabled')->willReturn($booleanComparator);
        $booleanComparator->compare($newFields['enabled'], $originalFields['enabled'])->willReturn(null);

        $comparatorRegistry->getFieldComparator('family')->willReturn($scalarComparator);
        $scalarComparator->compare($newFields['family'], $originalFields['family'])->willReturn(null);

        $comparatorRegistry->getFieldComparator('groups')->willReturn($arrayComparator);
        $arrayComparator->compare($newFields['groups'], $originalFields['groups'])->willReturn(null);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare($newFields['associations'], $originalFields['associations'])->willReturn(null);

        $this->filter($product, $newFields)->shouldReturn([]);
    }

    function it_throws_an_exception_if_code_is_not_found($normalizer, ProductInterface $product)
    {
        $originalFields = $newFields = [
            'other_field' => []
        ];

        $normalizer->normalize($product, 'standard')->willReturn($originalFields);

        $this->shouldThrow('LogicException')->during('filter', [$product, $newFields]);
    }
}
