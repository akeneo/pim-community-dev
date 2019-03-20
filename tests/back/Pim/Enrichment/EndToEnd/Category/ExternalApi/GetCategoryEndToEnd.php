<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Category\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetCategoryEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     */
    public function testGetACategory()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/master');

        $standardCategory = <<<JSON
{
    "code": "master",
    "parent": null,
    "labels": {}
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardCategory, $response->getContent());
    }

    public function testGetACompleteCategory()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/categoryA');

        $standardCategory = <<<JSON
{
    "code": "categoryA",
    "parent": "master",
    "labels": {
        "en_US": "Category A",
        "fr_FR": "Catégorie A"
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardCategory, $response->getContent());
    }

    public function testNotFoundACategory()
    {
        $client = $this->createAuthenticatedClient();

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
        return $this->catalog->useTechnicalCatalog();
    }
}
