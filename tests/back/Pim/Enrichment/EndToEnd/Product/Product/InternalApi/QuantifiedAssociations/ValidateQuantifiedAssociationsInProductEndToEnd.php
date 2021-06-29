<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi\QuantifiedAssociations;

use Symfony\Component\HttpFoundation\Response;

class ValidateQuantifiedAssociationsInProductEndToEnd extends AbstractProductWithQuantifiedAssociationsTestCase
{
    /**
     * @test
     */
    public function it_add_quantified_associations_to_a_product(): void
    {
        $product = $this->createProduct(
            'yellow_chair',
            'shoes',[
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
                        'identifier' => 'THIS_PRODUCT_DOES_NOT_EXISTS',
                        'quantity' => 3,
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
