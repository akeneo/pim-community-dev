<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Category;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ListCategoryIntegration extends TestCase
{
    public function testListCategories()
    {
        $client = static::createClient();

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
        $client = static::createClient();

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
