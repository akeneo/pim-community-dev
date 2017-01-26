<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Category;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListCategoryIntegration extends ApiTestCase
{
    public function testListCategories()
    {
        $client = $this->createAuthentifiedClient();

        $client->request('GET', 'api/rest/v1/categories');

        $standardCategories = [
            [
                'code'   => 'master',
                'parent' => null,
                'labels' => []
            ],
            [
                'code'   => 'categoryA',
                'parent' => 'master',
                'labels' => [
                    'en_US' => 'Category A',
                    'fr_FR' => 'Catégorie A'
                ]
            ],
            [
                'code'   => 'categoryA1',
                'parent' => 'categoryA',
                'labels' => []
            ],
            [
                'code'   => 'categoryA2',
                'parent' => 'categoryA',
                'labels' => []
            ],
            [
                'code'   => 'categoryB',
                'parent' => 'master',
                'labels' => []
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardCategories, json_decode($response->getContent(), true));
    }

    public function testOutOfRangeListCategories()
    {
        $client = $this->createAuthentifiedClient();

        $client->request('GET', 'api/rest/v1/categories?limit=10&page=2');

        $standardCategories = [];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardCategories, json_decode($response->getContent(), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
