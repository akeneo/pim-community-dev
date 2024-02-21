<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\QuantifiedAssociations;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class AddQuantifiedAssociationsToProductWithUuidEndToEnd extends AbstractProductTestCase
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
    public function it_add_quantified_associations_to_a_product(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $chairUuid = $this->createProduct('chair', [])->getUuid();
        $tableUuid = $this->createProduct('table', [])->getUuid();
        $this->createProductModel([
            'code' => 'umbrella',
            'family_variant' => 'familyVariantA1',
            'values' => [],
        ]);
        $identifier = 'garden_table_set';

        $data = <<<JSON
{
    "values": {
        "sku": [
            {"data": "$identifier", "locale": null, "scope": null}
        ]
    },
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"uuid": "{$chairUuid->toString()}", "quantity": 4},
                {"uuid": "{$tableUuid->toString()}", "quantity": 1}
            ],
            "product_models": [
                {"identifier": "umbrella", "quantity": 1}
            ]
        }
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => $identifier,
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => $identifier],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [
                'PRODUCTSET' => [
                    'products' => [
                        ['uuid' => $chairUuid->toString(), 'identifier' => 'chair', 'quantity' => 4],
                        ['uuid' => $tableUuid->toString(), 'identifier' => 'table', 'quantity' => 1],
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
        $this->assertSameProducts($expectedProduct, $identifier);
    }
}
