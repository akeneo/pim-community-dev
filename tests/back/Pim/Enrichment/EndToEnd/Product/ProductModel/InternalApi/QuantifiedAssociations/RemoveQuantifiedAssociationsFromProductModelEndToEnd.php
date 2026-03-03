<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\InternalApi\QuantifiedAssociations;

use Symfony\Component\HttpFoundation\Response;

class RemoveQuantifiedAssociationsFromProductModelEndToEnd extends AbstractProductModelWithQuantifiedAssociationsTestCase
{
    /**
     * @test
     */
    public function it_remove_quantified_associations_from_a_product_model(): void
    {
        $productModel = $this->createProductModel(
            [
                'code' => 'standard_chair',
                'family_variant' => 'accessories_size',
            ]
        );
        $normalizedProductModel = $this->getProductModelFromInternalApi($productModel->getId());

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

        $normalizedProductModelWithQuantifiedAssociations = $this->updateNormalizedProductModel(
            $normalizedProductModel,
            [
                'quantified_associations' => $quantifiedAssociations,
            ]
        );

        $this->updateProductModelWithInternalApi(
            $productModel->getId(),
            $normalizedProductModelWithQuantifiedAssociations
        );

        $normalizedProductModelWithoutQuantifiedAssociations = $this->updateNormalizedProductModel(
            $normalizedProductModel,
            [
                'quantified_associations' => [
                    'PRODUCTSET' => [
                        'products' => [],
                        'product_models' => [],
                    ],
                ],
            ]
        );

        $response = $this->updateProductModelWithInternalApi(
            $productModel->getId(),
            $normalizedProductModelWithoutQuantifiedAssociations
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertSame(
            [
                'PRODUCTSET' => [
                    'products' => [],
                    'product_models' => [],
                ],
            ],
            $body['quantified_associations']
        );
    }
}
