<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsMerger;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsFromAncestorsFilterSpec extends ObjectBehavior
{
    function let(
        QuantifiedAssociationsMerger $quantifiedAssociationsMerger
    ) {
        $this->beConstructedWith(
            $quantifiedAssociationsMerger
        );
    }

    public function it_remove_quantified_associations_on_products_belonging_to_an_ancestor(
        QuantifiedAssociationsMerger $quantifiedAssociationsMerger,
        ProductModelInterface $product_model,
        ProductModelInterface $variant_level_1,
        ProductInterface $variant_level_2
    ) {
        $product_model->getParent()->willReturn(null);
        $variant_level_1->getParent()->willReturn($product_model);
        $variant_level_2->getParent()->willReturn($variant_level_1);

        $product_model->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'product_A', 'quantity' => 2],
                    ],
                ],
            ]
        );
        $variant_level_1->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'product_B', 'quantity' => 3],
                    ],
                ],
            ]
        );

        $quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom(
            [
                $product_model,
                $variant_level_1,
            ]
        )->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'product_A', 'quantity' => 2],
                        ['identifier' => 'product_B', 'quantity' => 3],
                    ],
                ],
            ]
        );

        $mergedQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'product_A', 'quantity' => 2],
                    ['identifier' => 'product_B', 'quantity' => 3],
                    ['identifier' => 'product_C', 'quantity' => 4],
                ],
            ],
        ];

        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'product_C', 'quantity' => 4],
                ],
                'product_models' => [],
            ],
        ];

        $this->filter($mergedQuantifiedAssociations, $variant_level_2)->shouldReturn($expectedQuantifiedAssociations);
    }

    public function it_remove_quantified_associations_on_product_models_belonging_to_an_ancestor(
        QuantifiedAssociationsMerger $quantifiedAssociationsMerger,
        ProductModelInterface $product_model,
        ProductModelInterface $variant_level_1,
        ProductInterface $variant_level_2
    ) {
        $product_model->getParent()->willReturn(null);
        $variant_level_1->getParent()->willReturn($product_model);
        $variant_level_2->getParent()->willReturn($variant_level_1);

        $product_model->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'product_models' => [
                        ['identifier' => 'productmodel_A', 'quantity' => 2],
                    ],
                ],
            ]
        );
        $variant_level_1->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'product_models' => [
                        ['identifier' => 'productmodel_B', 'quantity' => 3],
                    ],
                ],
            ]
        );

        $quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom(
            [
                $product_model,
                $variant_level_1,
            ]
        )->willReturn(
            [
                'PACK' => [
                    'product_models' => [
                        ['identifier' => 'productmodel_A', 'quantity' => 2],
                        ['identifier' => 'productmodel_B', 'quantity' => 3],
                    ],
                ],
            ]
        );

        $mergedQuantifiedAssociations = [
            'PACK' => [
                'product_models' => [
                    ['identifier' => 'productmodel_A', 'quantity' => 2],
                    ['identifier' => 'productmodel_B', 'quantity' => 3],
                    ['identifier' => 'productmodel_C', 'quantity' => 4],
                ],
            ],
        ];

        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [
                    ['identifier' => 'productmodel_C', 'quantity' => 4],
                ],
            ],
        ];

        $this->filter($mergedQuantifiedAssociations, $variant_level_2)->shouldReturn($expectedQuantifiedAssociations);
    }

    public function it_preserve_quantified_associations_on_products_when_quantity_has_been_overwritten(
        QuantifiedAssociationsMerger $quantifiedAssociationsMerger,
        ProductModelInterface $product_model,
        ProductModelInterface $variant_level_1
    ) {
        $product_model->getParent()->willReturn(null);
        $variant_level_1->getParent()->willReturn($product_model);

        $product_model->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'product_A', 'quantity' => 2],
                    ],
                ],
            ]
        );

        $quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom(
            [
                $product_model,
            ]
        )->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'product_A', 'quantity' => 2],
                    ],
                ],
            ]
        );

        $mergedQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'product_A', 'quantity' => 42],
                ],
            ],
        ];

        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'product_A', 'quantity' => 42],
                ],
                'product_models' => [],
            ],
        ];

        $this->filter($mergedQuantifiedAssociations, $variant_level_1)->shouldReturn($expectedQuantifiedAssociations);
    }

    public function it_preserve_quantified_associations_on_product_models_when_quantity_has_been_overwritten(
        QuantifiedAssociationsMerger $quantifiedAssociationsMerger,
        ProductModelInterface $product_model,
        ProductModelInterface $variant_level_1
    ) {
        $product_model->getParent()->willReturn(null);
        $variant_level_1->getParent()->willReturn($product_model);

        $product_model->normalizeQuantifiedAssociations()->willReturn(
            [
                'PACK' => [
                    'product_models' => [
                        ['identifier' => 'productmodel_A', 'quantity' => 2],
                    ],
                ],
            ]
        );

        $quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom(
            [
                $product_model,
            ]
        )->willReturn(
            [
                'PACK' => [
                    'product_models' => [
                        ['identifier' => 'productmodel_A', 'quantity' => 2],
                    ],
                ],
            ]
        );

        $mergedQuantifiedAssociations = [
            'PACK' => [
                'product_models' => [
                    ['identifier' => 'productmodel_A', 'quantity' => 42],
                ],
            ],
        ];

        $expectedQuantifiedAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [
                    ['identifier' => 'productmodel_A', 'quantity' => 42],
                ],
            ],
        ];

        $this->filter($mergedQuantifiedAssociations, $variant_level_1)->shouldReturn($expectedQuantifiedAssociations);
    }
}
