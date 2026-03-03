<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\VariantProduct\ExternalApi\QuantifiedAssociations;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class AddQuantifiedAssociationsToVariantProductWithUuidEndToEnd extends AbstractProductTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /**
     * @test
     */
    public function it_adds_quantified_associations_to_a_variant_product(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->createProductModel([
            'code' => 'garden_table_set',
            'family_variant' => 'familyVariantA1',
            'values' => [],
        ]);

        $this->createProductModel([
            'code' => 'garden_table_set-black',
            'parent' => 'garden_table_set',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
            ],
        ]);

        $this->createProductModel([
            'code' => 'umbrella',
            'family_variant' => 'familyVariantA1',
            'values' => [],
        ]);

        $chairUuid = $this->createProduct('chair')->getUuid();

        $data = <<<JSON
{
    "parent": "garden_table_set-black",
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "garden_table_set-black-gold"}],
        "a_yes_no": [{"locale": null, "scope": null, "data": true}]
    },
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"uuid": "{$chairUuid->toString()}", "quantity": 8}
            ],
            "product_models": [
                {"identifier": "umbrella", "quantity": 2}
            ]
        }
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'garden_table_set-black-gold',
            'family' => 'familyA',
            'parent' => 'garden_table_set-black',
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                "a_simple_select" => [
                    ["data" => "optionB", "locale" => null, "scope" => null]
                ],
                "a_yes_no"=>[
                    ["data" => true, "locale" => null, "scope" => null]
                ],
                "sku" => [
                    ["data" => "garden_table_set-black-gold", "locale" => null, "scope" => null]
                ]
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [
                'PRODUCTSET' => [
                    'products' => [
                        ['uuid' => (string) $chairUuid, 'identifier' => 'chair', 'quantity' => 8],
                    ],
                    'product_models' => [
                        ['identifier' => 'umbrella', 'quantity' => 2],
                    ],
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'garden_table_set-black-gold');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
