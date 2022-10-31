<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi\QuantifiedAssociations;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
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
                new SetIdentifierValue('sku', 'yellow_chair')
            ]);
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

        $response = $this->updateProductWithInternalApi($product->getUuid(), $normalizedProductWithQuantifiedAssociations);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
