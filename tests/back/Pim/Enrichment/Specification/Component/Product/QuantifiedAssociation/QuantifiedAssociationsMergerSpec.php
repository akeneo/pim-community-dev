<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationsMergerSpec extends ObjectBehavior
{
    public function it_merge_quantified_associations(
        ProductInterface $product_1,
        ProductInterface $product_2
    ) {
        $product_1->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                ],
            ],
        ]);
        $product_2->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'C', 'quantity' => 4],
                    ['identifier' => 'D', 'quantity' => 5],
                ],
            ],
        ]);

        $this->normalizeAndMergeQuantifiedAssociationsFrom([
            $product_1,
            $product_2,
        ])->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 4],
                    ['identifier' => 'D', 'quantity' => 5],
                ],
            ],
        ]);
    }

    public function it_merge_quantified_associations_and_overwrite_quantities_from_duplicated_identifiers(
        ProductInterface $product_1,
        ProductInterface $product_2
    ) {
        $product_1->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 4],
                ],
            ],
        ]);
        $product_2->normalizeQuantifiedAssociations()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'C', 'quantity' => 42],
                    ['identifier' => 'D', 'quantity' => 5],
                ],
            ],
        ]);

        $this->normalizeAndMergeQuantifiedAssociationsFrom([
            $product_1,
            $product_2,
        ])->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 42],
                    ['identifier' => 'D', 'quantity' => 5],
                ],
            ],
        ]);
    }
}
