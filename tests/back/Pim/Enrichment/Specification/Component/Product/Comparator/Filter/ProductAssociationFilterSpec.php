<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAssociationFilterSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, ComparatorRegistry $comparatorRegistry)
    {
        $this->beConstructedWith($normalizer, $comparatorRegistry);
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf(FilterInterface::class);
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

        $normalizer->normalize($product, 'standard')
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
        $originalValues = [
            'PACK' => [
                'groups'   => ['oro_tshirt'],
                'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
            ]
        ];
        $newValues = [
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                ]
            ]
        ];

        $normalizer->normalize($product, 'standard')
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['groups'],
            $originalValues['PACK']['groups']
        )->willReturn($newValues['associations']['PACK']['groups']);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['products'],
            $originalValues['PACK']['products']
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
        $originalValues = [
            'PACK' => [
                'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
            ]
        ];

        $newValues = [
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                ]
            ]
        ];

        $normalizer->normalize($product, 'standard')
            ->willReturn($originalValues);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['groups'],
            $originalValues['PACK']['groups']
        )->willReturn(null);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['products'],
            $originalValues['PACK']['products']
        )->willReturn(null);

        $this->filter($product, $newValues)->shouldReturn([]);
    }
}
