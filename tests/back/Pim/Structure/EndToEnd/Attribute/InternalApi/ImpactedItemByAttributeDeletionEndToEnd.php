<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use AkeneoTest\Pim\Structure\EndToEnd\InternalApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ImpactedItemByAttributeDeletionEndToEnd extends InternalApiTestCase
{
    private Client $elasticsearchClient;
    private EntityBuilder $entityBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elasticsearchClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $this->loadFixtures();
    }

    public function testItReturnNumberOfProductAndProductModelImpacted(): void
    {
        $expectedResponse = <<<JSON
{
    "products": 2,
    "product_models": 1
}
JSON;

        $this->authenticateAsAdminUser();
        $this->client->request('GET', 'rest/attribute/a_simple_select/impacted_items_by_deletion');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function loadFixtures(): void
    {
        $productModel = $this->entityBuilder->createProductModel('product_model', 'familyVariantA1', null, []);
        $this->entityBuilder->createProductModel('sub_product_model', 'familyVariantA1', $productModel, [
            'values' => [
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
            ],
        ]);

        $productModelA2 = $this->entityBuilder->createProductModel('another_product_model', 'familyVariantA2', null, []);
        $this->entityBuilder->createVariantProduct('variant_product', 'familyA', 'familyVariantA2', $productModelA2, [
            'values' => [
                'a_simple_select' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'optionA',
                    ],
                ],
                'a_yes_no' =>  [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => true,
                    ],
                ],
            ],
        ]);

        $this->entityBuilder->createProduct('a_product', 'familyA', [
            'values' => [
                'a_simple_select' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'optionA',
                    ],
                ],
                'a_yes_no' =>  [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => true,
                    ],
                ],
            ],
        ]);

        $this->elasticsearchClient->refreshIndex();
    }
}
