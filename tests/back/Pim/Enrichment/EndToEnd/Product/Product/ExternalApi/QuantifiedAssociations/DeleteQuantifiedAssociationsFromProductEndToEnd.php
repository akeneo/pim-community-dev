<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\QuantifiedAssociations;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteQuantifiedAssociationsFromProductEndToEnd extends AbstractProductTestCase
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
    public function it_delete_quantified_associations_from_a_product(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->createProduct('table', []);
        $this->createProductModel([
            'code' => 'umbrella',
            'family_variant' => 'familyVariantA1',
            'values' => [],
        ]);
        $identifier = 'garden_table_set';

        $data = <<<JSON
{
    "identifier": "$identifier",
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"identifier": "table", "quantity": 1}
            ],
            "product_models": [
                {"identifier": "umbrella", "quantity": 1}
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
        "PRODUCTSET": {
            "products": [],
            "product_models": []
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
            'values' => new \stdClass(),
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => ['groups' => [],'product_models' => [],'products' => []],
                'SUBSTITUTION' => ['groups' => [],'product_models' => [],'products' => []],
                'UPSELL' => ['groups' => [],'product_models' => [],'products' => []],
                'X_SELL' => ['groups' => [],'product_models' => [],'products' => []],
            ],
            'quantified_associations' => new \stdClass(),
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, $identifier);
    }
}
