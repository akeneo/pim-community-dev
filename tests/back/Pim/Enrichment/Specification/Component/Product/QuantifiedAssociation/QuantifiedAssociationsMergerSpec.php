<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationsMergerSpec extends ObjectBehavior
{
    public function it_merge_quantified_associations(
        ProductInterface $product1,
        ProductInterface $product2,
        QuantifiedAssociationCollection $quantifiedAssociations1,
        QuantifiedAssociationCollection $quantifiedAssociations2,
        QuantifiedAssociationCollection $quantifiedAssociationsMerged
    ) {
        $product1->getQuantifiedAssociations()->willReturn($quantifiedAssociations1);
        $product2->getQuantifiedAssociations()->willReturn($quantifiedAssociations2);

        $quantifiedAssociations1->merge($quantifiedAssociations2)->shouldBeCalled()->willReturn($quantifiedAssociationsMerged);
        $quantifiedAssociationsMerged->normalize()->willReturn(
            [
                'PACK' => [
                    'products' => [
                        ['identifier' => 'A', 'quantity' => 2],
                        ['identifier' => 'B', 'quantity' => 3],
                        ['identifier' => 'C', 'quantity' => 42],
                        ['identifier' => 'D', 'quantity' => 5],
                    ],
                    'product_models' => [],
                ],
            ]
        );

        $this->normalizeAndMergeQuantifiedAssociationsFrom([
            $product1,
            $product2,
        ])->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 42],
                    ['identifier' => 'D', 'quantity' => 5],
                ],
                'product_models' => [],
            ],
        ]);
    }
}
