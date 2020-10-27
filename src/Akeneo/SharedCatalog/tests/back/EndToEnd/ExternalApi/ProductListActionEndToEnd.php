<?php

namespace Akeneo\SharedCatalog\tests\back\EndToEnd\ExternalApi;

use Akeneo\SharedCatalog\tests\back\Utils\CreateJobInstance;
use Akeneo\SharedCatalog\tests\back\Utils\CreateProduct;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class ProductListActionEndToEnd extends ApiTestCase
{
    use CreateJobInstance;
    use CreateProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProduct('productA', 'aFamily', []);
        $this->createProduct('productB', 'aFamily', []);
        $this->createProduct('productC', 'aFamily', []);
        $this->createProduct('productD', 'aFamily', []);
        $this->createProduct('productE', 'aFamily', []);
        $this->createProduct('productF', 'aFamily', []);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_list_product_identifiers_linked_to_catalog()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'akeneo_shared_catalog',
            'export',
            JobInstance::STATUS_READY,
            [
                'recipients' => [],
                'filters' => [
                    'structure' => [
                        'scope' => 'mobile',
                        'locales' => [
                            'en_US',
                        ],
                        'attributes' => [
                            'name',
                        ],
                    ],
                ],
                'branding' => [],
            ]
        );

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/shared-catalogs/shared_catalog_1/products');
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [
                'results' => [
                    'productA',
                    'productB',
                    'productC',
                    'productD',
                    'productE',
                    'productF',
                ],
            ],
            json_decode($response->getContent(), true)
        );
    }

    /**
     * @test
     */
    public function it_paginate_product_id_linked_to_catalog()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'akeneo_shared_catalog',
            'export',
            JobInstance::STATUS_READY,
            [
                'recipients' => [],
                'filters' => [
                    'structure' => [
                        'scope' => 'mobile',
                        'locales' => [
                            'en_US',
                        ],
                        'attributes' => [
                            'name',
                        ],
                    ],
                ],
                'branding' => [],
            ]
        );

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/shared-catalogs/shared_catalog_1/products?limit=2');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [
                'results' => [
                    'productA',
                    'productB',
                ],
            ],
            json_decode($response->getContent(), true)
        );

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/shared-catalogs/shared_catalog_1/products?limit=3&search_after=productB');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [
                'results' => [
                    'productC',
                    'productD',
                    'productE'
                ],
            ],
            json_decode($response->getContent(), true)
        );
    }
}
