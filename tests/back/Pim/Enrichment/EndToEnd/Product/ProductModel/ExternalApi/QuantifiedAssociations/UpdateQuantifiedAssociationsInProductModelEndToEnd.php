<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi\QuantifiedAssociations;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi\AbstractProductModelTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateQuantifiedAssociationsInProductModelEndToEnd extends AbstractProductModelTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_can_partial_update_quantified_associations_in_a_product_model(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET_A');
        $this->createQuantifiedAssociationType('PRODUCTSET_B');
        $productChair = $this->createProduct('chair', []);
        $code = 'garden_table_set';

        $data = <<<JSON
{
    "code": "$code",
    "family_variant": "familyVariantA1",
    "quantified_associations": {
        "PRODUCTSET_A": {
            "products": [
                {"identifier": "chair", "quantity": 4}
            ]
        },
        "PRODUCTSET_B": {
            "products": [
                {"identifier": "chair", "quantity": 4}
            ]
        }
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/product-models', [], [], [], $data);

        $data = <<<JSON
{
    "code": "$code",
    "quantified_associations": {
        "PRODUCTSET_A": {
            "products": [
                {"identifier": "chair", "quantity": 6}
            ]
        }
    }
}
JSON;

        $client->request('PATCH', sprintf('/api/rest/v1/product-models/%s', $code), [], [], [], $data);

        $expectedProductModel = [
            'code' => $code,
            'family_variant' => 'familyVariantA1',
            'parent' => null,
            'categories' => [],
            'values' => [],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [
                'PRODUCTSET_A' => [
                    'products' => [
                        ['uuid' => (string) $productChair->getUuid(), 'identifier' => 'chair', 'quantity' => 6],
                    ],
                    'product_models' => [],
                ],
                'PRODUCTSET_B' => [
                    'products' => [
                        ['uuid' => (string) $productChair->getUuid(), 'identifier' => 'chair', 'quantity' => 4],
                    ],
                    'product_models' => [],
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, $code);
    }
}
