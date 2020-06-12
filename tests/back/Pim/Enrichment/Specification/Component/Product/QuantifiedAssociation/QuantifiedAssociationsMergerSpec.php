<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationsMergerSpec extends ObjectBehavior
{
    public function it_merge_quantified_associations(
        ProductInterface $product_1,
        ProductInterface $product_2,
        QuantifiedAssociations $quantifiedAsociations_1,
        QuantifiedAssociations $quantifiedAsociations_2
    ) {
        $product_1->getQuantifiedAssociations()->willReturn($quantifiedAsociations_1);
        $product_2->getQuantifiedAssociations()->willReturn($quantifiedAsociations_2);

        $quantifiedAsociations_1->merge($quantifiedAsociations_1)->shouldBeCalled();
        $quantifiedAsociations_1->merge($quantifiedAsociations_2)->shouldBeCalled();
        $quantifiedAsociations_1->normalize()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
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
