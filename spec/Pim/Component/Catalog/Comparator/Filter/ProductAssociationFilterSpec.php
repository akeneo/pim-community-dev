<?php

namespace spec\Pim\Component\Catalog\Comparator\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Comparator\ComparatorInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAssociationFilterSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, ComparatorRegistry $comparatorRegistry)
    {
        $this->beConstructedWith($normalizer, $comparatorRegistry);
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface');
    }

    function it_returns_all_associations_on_a_new_product(
        $normalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $arrayComparator
    ) {
        $originalValues = ['associations' => []];
        $newValues = [
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                ]
            ]
        ];

        $normalizer->normalize($product, 'json', ['only_associations' => true])
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare($newValues['associations']['PACK']['groups'], [])
            ->willReturn($newValues['associations']['PACK']['groups']);
        $arrayComparator->compare($newValues['associations']['PACK']['products'], [])
            ->willReturn($newValues['associations']['PACK']['products']);

        $this->filter($product, $newValues)->shouldReturn($newValues);
    }

    function it_filters_not_updated_values(
        $normalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $arrayComparator
    ) {
        $originalValues = ['associations' => [
            'PACK' => [
                'groups'   => ['oro_tshirt'],
                'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
            ]
        ]];
        $newValues = [
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                ]
            ]
        ];

        $normalizer->normalize($product, 'json', ['only_associations' => true])
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['groups'],
            $originalValues['associations']['PACK']['groups']
        )->willReturn($newValues['associations']['PACK']['groups']);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['products'],
            $originalValues['associations']['PACK']['products']
        )->willReturn(null);

        $this->filter($product, $newValues)->shouldReturn([
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                ]
            ]
        ]);
    }

    function it_returns_an_empty_array_when_new_and_original_products_are_equals(
        $normalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $arrayComparator
    ) {
        $originalValues = $newValues = [
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                ]
            ]
        ];

        $normalizer->normalize($product, 'json', ['only_associations' => true])
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['groups'],
            $originalValues['associations']['PACK']['groups']
        )->willReturn(null);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['products'],
            $originalValues['associations']['PACK']['products']
        )->willReturn(null);

        $this->filter($product, $newValues)->shouldReturn([]);
    }

    function it_throws_an_exception_if_new_values_contain_other_than_associations(
        $normalizer,
        ProductInterface $product
    ) {
        $originalValues = $newValues = [
            'family' => [],
            'associations' => ['groups' => [1]],
        ];

        $normalizer->normalize($product, 'json', ['only_associations' => true])
            ->willReturn($originalValues);

        $this
            ->shouldThrow('LogicException')
            ->during(
                'filter',
                [$product, $newValues]
            );
    }
}
