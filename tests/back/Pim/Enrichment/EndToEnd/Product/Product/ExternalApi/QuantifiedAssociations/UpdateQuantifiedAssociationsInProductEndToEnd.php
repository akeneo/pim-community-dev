<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\QuantifiedAssociations;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateQuantifiedAssociationsInProductEndToEnd extends AbstractProductTestCase
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
    public function it_can_partial_update_quantified_associations_in_a_product(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET_A');
        $this->createQuantifiedAssociationType('PRODUCTSET_B');
        $this->createQuantifiedAssociationType('1234');
        $productChair = $this->createProduct('chair', []);
        $identifier = 'garden_table_set';

        $data = <<<JSON
{
    "identifier": "$identifier",
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
        },
        "1234": {
            "products": [
                {"identifier": "chair", "quantity": 2}
            ]
        }
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);

        $data = <<<JSON
{
    "identifier": "$identifier",
    "quantified_associations": {
        "PRODUCTSET_A": {
            "products": [
                {"identifier": "chair", "quantity": 6}
            ]
        }
    }
}
JSON;

        $client->request('PATCH', sprintf('/api/rest/v1/products/%s', $identifier), [], [], [], $data);

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
                '1234' => [
                    'products' => [
                        ['uuid' => (string) $productChair->getUuid(), 'identifier' => 'chair', 'quantity' => 2],
                    ],
                    'product_models' => [],
                ],
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
        $this->assertSameProducts($expectedProduct, $identifier);
    }
}
