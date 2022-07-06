<?php

namespace AkeneoTest\Pim\Enrichment\Specification\Component\Product\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;

class QuantifiedAssociationsWithUuidNormalizerSpec extends ObjectBehavior
{
    function it_should_normalize_associations()
    {
        $this->normalize(
            [
                'X_SELL' => [
                    'products' => [
                        ['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product1', 'quantity' => 42],
                        ['uuid' => 'bc867b75-0ed7-410c-8d32-7a25da622952', 'identifier' => 'product2', 'quantity' => 69],
                    ],
                    'product_models' => ['productModel1', 'productModel2'],
                ],
                'UPSELL' => [
                    'products' => [
                        ['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product1', 'quantity' => 1],
                        ['uuid' => 'bc867b75-0ed7-410c-8d32-7a25da622952', 'identifier' => 'product2', 'quantity' => 2],
                    ],
                    'product_models' => ['productModel1', 'productModel2'],
                ],
            ]
        )->shouldReturn(
            [
                'X_SELL' => [
                    'products' => [
                        ['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'quantity' => 42],
                        ['uuid' => 'bc867b75-0ed7-410c-8d32-7a25da622952', 'quantity' => 69],
                    ],
                    'product_models' => ['productModel1', 'productModel2'],
                ],
                'UPSELL' => [
                    'products' => [
                        ['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'quantity' => 1],
                        ['uuid' => 'bc867b75-0ed7-410c-8d32-7a25da622952', 'quantity' => 2],
                    ],
                    'product_models' => ['productModel1', 'productModel2'],
                ],
            ]
        );
    }
}
