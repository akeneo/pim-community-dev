<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Category;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetCategoryIntegration extends ApiTestCase
{
    public function testGetACategory()
    {
        $client = $this->createAuthentifiedClient();

        $client->request('GET', 'api/rest/v1/categories/master');

        $standardCategory = [
            'code'   => 'master',
            'parent' => null,
            'labels' => []
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardCategory, json_decode($response->getContent(), true));
    }

    public function testGetACompleteCategory()
    {
        $client = $this->createAuthentifiedClient();

        $client->request('GET', 'api/rest/v1/categories/categoryA');

        $standardCategory = [
            'code'   => 'categoryA',
            'parent' => 'master',
            'labels' => [
                'en_US' => 'Category A',
                'fr_FR' => 'Catégorie A'
            ]
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardCategory, json_decode($response->getContent(), true));
    }

    public function testNotFoundACategory()
    {
        $client = $this->createAuthentifiedClient();

        $client->request('GET', 'api/rest/v1/categories/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Category "not_found" does not exist.', $content['message']);
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
