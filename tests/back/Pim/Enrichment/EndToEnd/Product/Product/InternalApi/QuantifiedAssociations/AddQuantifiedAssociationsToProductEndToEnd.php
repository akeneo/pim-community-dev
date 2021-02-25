<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi\QuantifiedAssociations;

use Symfony\Component\HttpFoundation\Response;

class AddQuantifiedAssociationsToProductEndToEnd extends AbstractProductWithQuantifiedAssociationsTestCase
{
    /**
     * @test
     */
    public function it_add_quantified_associations_to_a_product(): void
    {
        $product = $this->createProduct(
            'yellow_chair',
            null,
            [
                'values' => [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'yellow_chair',
                        ],
                    ],
                ],
            ]);
        $normalizedProduct = $this->getProductFromInternalApi($product->getId());

        $quantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    [
                        'identifier' => '1111111111',
                        'quantity' => 3,
                    ],
                ],
                'product_models' => [
                    [
                        'identifier' => 'amor',
                        'quantity' => 42,
                    ],
                ],
            ],
        ];

        $normalizedProductWithQuantifiedAssociations = $this->updateNormalizedProduct(
            $normalizedProduct,
            [
                'quantified_associations' => $quantifiedAssociations,
            ]
        );

        $response = $this->updateProductWithInternalApi($product->getId(), $normalizedProductWithQuantifiedAssociations);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertSame($body['quantified_associations'], $quantifiedAssociations);
    }
}
