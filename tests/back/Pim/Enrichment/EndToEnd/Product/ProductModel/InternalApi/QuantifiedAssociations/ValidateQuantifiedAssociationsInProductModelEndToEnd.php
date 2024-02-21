<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\InternalApi\QuantifiedAssociations;

use Symfony\Component\HttpFoundation\Response;

class ValidateQuantifiedAssociationsInProductModelEndToEnd extends AbstractProductModelWithQuantifiedAssociationsTestCase
{
    /**
     * @test
     */
    public function it_add_quantified_associations_to_a_product_model(): void
    {
        $productModel = $this->createProductModel([
            'code' => 'standard_chair',
            'family_variant' => 'accessories_size',
        ]);
        $normalizedProductModel = $this->getProductModelFromInternalApi($productModel->getId());

        $quantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    [
                        'identifier' => 'THIS_PRODUCT_DOES_NOT_EXISTS',
                        'quantity' => 3,
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

        $response = $this->updateProductModelWithInternalApi(
            $productModel->getId(),
            $normalizedProductModelWithQuantifiedAssociations
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertContainsError($body['quantified_associations'], [
            'messageTemplate' => 'pim_catalog.constraint.quantified_associations.products_do_not_exist',
            'propertyPath' => 'quantifiedAssociations.PRODUCTSET.products',
        ]);
    }

    private function assertContainsError(array $errors, $expectedError): void
    {
        foreach ($errors as $error) {
            if ($expectedError === array_intersect_assoc($error, $expectedError)) {
                return;
            }
        }

        $this->fail('Error not found');
    }
}
