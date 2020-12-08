<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Attribute\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CountItemsWithAttributeValueEndToEnd extends InternalApiTestCase
{
    private Client $elasticsearchClient;
    private EntityBuilder $entityBuilder;
    private UserRepositoryInterface $userRepository;

    private const ENDPOINT_URL = 'rest/product-and-product-model/count_items_with_attribute_value';

    protected function setUp(): void
    {
        parent::setUp();

        $this->elasticsearchClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $this->userRepository = $this->get('pim_user.repository.user');

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

        $this->authenticate($this->getAdminUser());
        $this->client->request('GET', self::ENDPOINT_URL . '?attribute_code=a_simple_select');

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

    private function getAdminUser(): UserInterface
    {
        return $this->userRepository->findOneByIdentifier('admin');
    }
}
