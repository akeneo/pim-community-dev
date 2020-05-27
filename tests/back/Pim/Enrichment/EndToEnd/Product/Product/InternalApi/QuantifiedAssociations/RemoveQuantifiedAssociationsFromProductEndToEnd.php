<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\InternalApi;

use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi\QuantifiedAssociations\AbstractProductWithQuantifiedAssociationsTestCase;
use Symfony\Component\HttpFoundation\Response;

class RemoveQuantifiedAssociationsFromProductEndToEnd extends AbstractProductWithQuantifiedAssociationsTestCase
{
    /**
     * @test
     */
    public function it_remove_quantified_associations_from_a_product(): void
    {
        $product = $this->createProduct([
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

        $this->updateProductWithInternalApi($product->getId(), $normalizedProductWithQuantifiedAssociations);

        $normalizedProductWithoutQuantifiedAssociations = $this->updateNormalizedProduct(
            $normalizedProduct,
            [
                'quantified_associations' => [],
            ]
        );

        $response = $this->updateProductWithInternalApi($product->getId(), $normalizedProductWithoutQuantifiedAssociations);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertSame($body['quantified_associations'], []);
    }
}
