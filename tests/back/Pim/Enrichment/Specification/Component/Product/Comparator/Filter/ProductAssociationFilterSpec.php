<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAssociationFilterSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $associationsNormalizer,
        NormalizerInterface $quantifiedAssociationsNormalizer,
        ComparatorRegistry $comparatorRegistry
    ) {
        $this->beConstructedWith(
            $associationsNormalizer,
            $quantifiedAssociationsNormalizer,
            $comparatorRegistry,
        );
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf(FilterInterface::class);
    }

    function it_returns_all_associations_on_a_new_product(
        $associationsNormalizer,
        $quantifiedAssociationsNormalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $arrayComparator
    ) {
        $originalAssociationsValues = ['associations' => []];
        $originalQuantifiedAssociationsValues = ['quantified_associations' => []];
        $newValues = [
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                ]
            ],
            'quantified_associations' => [
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'sku-A', 'quantity' => '12'],
                        ['identifier' => 'sku-B', 'quantity' => '24']
                    ],
                    'product_models' => [
                        ['identifier' => 'sku_model-A', 'quantity' => '2'],
                        ['identifier' => 'sku_model-B', 'quantity' => '4']
                    ]
                ]
            ]
        ];

        $associationsNormalizer->normalize($product, 'standard', ['with_association_uuids' => false])
            ->willReturn($originalAssociationsValues);
        $quantifiedAssociationsNormalizer->normalize($product, 'standard')
            ->willReturn($originalQuantifiedAssociationsValues);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare($newValues['associations']['PACK']['groups'], [])
            ->willReturn($newValues['associations']['PACK']['groups']);
        $arrayComparator->compare($newValues['associations']['PACK']['products'], [])
            ->willReturn($newValues['associations']['PACK']['products']);

        $comparatorRegistry->getFieldComparator('quantified_associations')->willReturn($arrayComparator);
        $arrayComparator->compare($newValues['quantified_associations']['PRODUCTSET']['product_models'], [])
            ->willReturn($newValues['quantified_associations']['PRODUCTSET']['product_models']);
        $arrayComparator->compare($newValues['quantified_associations']['PRODUCTSET']['products'], [])
            ->willReturn($newValues['quantified_associations']['PRODUCTSET']['products']);

        $this->filter($product, $newValues)->shouldReturn(
            [
                'associations' => [
                    'PACK' => [
                        'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                        'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                    ]
                ],
                'quantified_associations' => [
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'sku-A', 'quantity' => '12'],
                            ['identifier' => 'sku-B', 'quantity' => '24']
                        ],
                        'product_models' => [
                            ['identifier' => 'sku_model-A', 'quantity' => '2'],
                            ['identifier' => 'sku_model-B', 'quantity' => '4']
                        ]
                    ]
                ]
            ]
        );
    }

    function it_filters_not_updated_values(
        $associationsNormalizer,
        $quantifiedAssociationsNormalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $arrayComparator
    ) {
        $originalAssociationsValues = [
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
            ],
            'quantified_associations' => []
        ];

        $associationsNormalizer->normalize($product, 'standard', ['with_association_uuids' => false])
            ->willReturn($originalAssociationsValues);
        $quantifiedAssociationsNormalizer->normalize($product, 'standard')
            ->willReturn(['quantified_associations' => []]);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['groups'],
            $originalAssociationsValues['PACK']['groups']
        )->willReturn($newValues['associations']['PACK']['groups']);
        $arrayComparator->compare(
            $newValues['associations']['PACK']['products'],
            $originalAssociationsValues['PACK']['products']
        )->willReturn(null);

        $this->filter($product, $newValues)->shouldReturn([
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                ]
            ]
        ]);
    }

    function it_returns_an_empty_array_when_new_and_original_products_associations_are_equals(
        $associationsNormalizer,
        $quantifiedAssociationsNormalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $arrayComparator
    ) {
        $originalAssociationsValues = [
            'PACK' => [
                'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
            ]
        ];

        $newAssociationsValues = [
            'associations' => [
                'PACK' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKNTS_BPXS', 'AKNTS_BPS', 'AKNTS_BPM']
                ]
            ],
            'quantified_associations' => []
        ];

        $associationsNormalizer->normalize($product, 'standard', ['with_association_uuids' => false])
            ->willReturn($originalAssociationsValues);
        $quantifiedAssociationsNormalizer->normalize($product, 'standard')
            ->willReturn(['quantified_associations' => []]);

        $comparatorRegistry->getFieldComparator('associations')->willReturn($arrayComparator);
        $arrayComparator->compare(
            $newAssociationsValues['associations']['PACK']['groups'],
            $originalAssociationsValues['PACK']['groups']
        )->willReturn(null);
        $arrayComparator->compare(
            $newAssociationsValues['associations']['PACK']['products'],
            $originalAssociationsValues['PACK']['products']
        )->willReturn(null);

        $this->filter($product, $newAssociationsValues)->shouldReturn([]);
    }

    function it_returns_an_empty_array_when_new_and_original_products_quantified_associations_are_equals(
        $associationsNormalizer,
        $quantifiedAssociationsNormalizer,
        $comparatorRegistry,
        ProductInterface $product,
        ComparatorInterface $arrayComparator
    ) {
        $originalQuantifiedAssociationsValues = [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'sku-A', 'quantity' => '12'],
                    ['identifier' => 'sku-B', 'quantity' => '24']
                ],
                'product_models' => [
                    ['identifier' => 'sku_model-A', 'quantity' => '2'],
                    ['identifier' => 'sku_model-B', 'quantity' => '4']
                ]
            ]
        ];

        $newQuantifiedAssociationsValues = [
            'associations' => [],
            'quantified_associations' => [
                'PRODUCTSET' => [
                    'products' => [
                        ['identifier' => 'sku-A', 'quantity' => '12'],
                        ['identifier' => 'sku-B', 'quantity' => '24']
                    ],
                    'product_models' => [
                        ['identifier' => 'sku_model-A', 'quantity' => '2'],
                        ['identifier' => 'sku_model-B', 'quantity' => '4']
                    ]
                ]
            ]
        ];

        $associationsNormalizer->normalize($product, 'standard', ['with_association_uuids' => false])
            ->willReturn(['associations' => []]);
        $quantifiedAssociationsNormalizer->normalize($product, 'standard')
            ->willReturn($originalQuantifiedAssociationsValues);

        $comparatorRegistry->getFieldComparator('quantified_associations')->willReturn($arrayComparator);
        $arrayComparator->compare(
            $newQuantifiedAssociationsValues['quantified_associations']['PRODUCTSET']['product_models'],
            $originalQuantifiedAssociationsValues['PRODUCTSET']['product_models']
        )->willReturn(null);
        $arrayComparator->compare(
            $newQuantifiedAssociationsValues['quantified_associations']['PRODUCTSET']['products'],
            $originalQuantifiedAssociationsValues['PRODUCTSET']['products']
        )->willReturn(null);

        $this->filter($product, $newQuantifiedAssociationsValues)->shouldReturn([]);
    }

    function it_cannot_filter_with_both_products_and_product_uuids(
        ProductInterface $product,
    ) {
        $uuid1 = Uuid::uuid4();
        $newValues = [
            'associations' => [
                'XSELL' => [
                    'product_uuids' => [$uuid1->toString()],
                    'products' => ['a_product_identifier']
                ],
            ],
        ];
        $this->shouldThrow(\LogicException::class)->during('filter', [$product, $newValues]);
    }
}
