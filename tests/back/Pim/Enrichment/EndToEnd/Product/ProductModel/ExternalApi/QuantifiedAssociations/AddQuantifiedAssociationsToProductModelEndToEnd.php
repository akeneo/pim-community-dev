<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi\QuantifiedAssociations;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi\AbstractProductModelTestCase;
use Symfony\Component\HttpFoundation\Response;

class AddQuantifiedAssociationsToProductModelEndToEnd extends AbstractProductModelTestCase
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
    public function it_add_quantified_associations_to_a_product_model(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $chair = $this->createProduct('chair', []);
        $table = $this->createProduct('table', []);
        $this->createProductModel([
            'code' => 'umbrella',
            'family_variant' => 'familyVariantA1',
            'values' => [],
        ]);
        $code = 'garden_table_set';

        $data = <<<JSON
{
    "code": "$code",
    "family_variant": "familyVariantA1",
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"identifier": "chair", "quantity": 4},
                {"identifier": "table", "quantity": 1}
            ],
            "product_models": [
                {"identifier": "umbrella", "quantity": 1}
            ]
        }
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/product-models', [], [], [], $data);

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
                'PRODUCTSET' => [
                    'products' => [
                        ['uuid' => (string) $chair->getUuid(), 'identifier' => 'chair', 'quantity' => 4],
                        ['uuid' => (string) $table->getUuid(), 'identifier' => 'table', 'quantity' => 1],
                    ],
                    'product_models' => [
                        ['identifier' => 'umbrella', 'quantity' => 1],
                    ],
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, $code);
    }
}
