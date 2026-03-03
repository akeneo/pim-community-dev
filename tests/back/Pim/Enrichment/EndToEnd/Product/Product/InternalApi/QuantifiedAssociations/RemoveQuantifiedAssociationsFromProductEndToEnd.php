<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi\QuantifiedAssociations;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Symfony\Component\HttpFoundation\Response;

class RemoveQuantifiedAssociationsFromProductEndToEnd extends AbstractProductWithQuantifiedAssociationsTestCase
{
    /**
     * @test
     */
    public function it_remove_quantified_associations_from_a_product(): void
    {
        $product = $this->createProduct(
            'yellow_chair',
            null,
            [
                new SetIdentifierValue('sku', 'yellow_chair')
            ]
        );
        $normalizedProduct = $this->getProductFromInternalApi($product->getUuid());

        $quantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    [
                        'uuid' => $this->getProductUuid('1111111111')->toString(),
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

        $this->updateProductWithInternalApi($product->getUuid(), $normalizedProductWithQuantifiedAssociations);

        $normalizedProductWithoutQuantifiedAssociations = $this->updateNormalizedProduct(
            $normalizedProduct,
            [
                'quantified_associations' => [
                    'PRODUCTSET' => [
                        'products' => [],
                        'product_models' => [],
                    ],
                ],
            ]
        );

        $response = $this->updateProductWithInternalApi(
            $product->getUuid(),
            $normalizedProductWithoutQuantifiedAssociations
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
