<?php

namespace Akeneo\SharedCatalog\tests\back\EndToEnd\ExternalApi;

use Akeneo\SharedCatalog\tests\back\Utils\CreateJobInstance;
use Akeneo\SharedCatalog\tests\back\Utils\CreateProduct;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class ProductUuidListActionEndToEnd extends ApiTestCase
{
    use CreateJobInstance;
    use CreateProduct;

    private array $sortedProductUuids = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->sortedProductUuids[] = $this->createProduct('productA', 'aFamily', [])->getUuid()->toString();
        $this->sortedProductUuids[] = $this->createProduct('productB', 'aFamily', [])->getUuid()->toString();
        $this->sortedProductUuids[] = $this->createProduct('productC', 'aFamily', [])->getUuid()->toString();
        $this->sortedProductUuids[] = $this->createProduct('productD', 'aFamily', [])->getUuid()->toString();
        $this->sortedProductUuids[] = $this->createProduct('productE', 'aFamily', [])->getUuid()->toString();
        $this->sortedProductUuids[] = $this->createProduct('productF', 'aFamily', [])->getUuid()->toString();
        sort($this->sortedProductUuids);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /** @test */
    public function it_list_product_identifiers_linked_to_catalog()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'Shared catalog 1',
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

        $client->request('GET', 'api/rest/v1/shared-catalogs/shared_catalog_1/product-uuids');
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [
                'results' => $this->sortedProductUuids,
            ],
            json_decode($response->getContent(), true)
        );
    }

    /** @test */
    public function it_paginate_product_id_linked_to_catalog()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'Shared catalog 1',
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
        $client->request('GET', 'api/rest/v1/shared-catalogs/shared_catalog_1/product-uuids?limit=2');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [
                'results' => \array_slice($this->sortedProductUuids, 0, 2),
            ],
            json_decode($response->getContent(), true)
        );

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $secondProductUuid = $this->sortedProductUuids[1];
        $client->request('GET', 'api/rest/v1/shared-catalogs/shared_catalog_1/product-uuids?limit=3&search_after=' . $secondProductUuid);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [
                'results' => \array_slice($this->sortedProductUuids, 2, 3),
            ],
            json_decode($response->getContent(), true)
        );
    }
}
