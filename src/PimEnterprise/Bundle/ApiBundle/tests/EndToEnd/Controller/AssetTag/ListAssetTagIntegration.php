<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetTag;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ListAssetTagIntegration extends AbstractAssetTagTestCase
{
    public function testListTagsWithoutLimitAndWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/asset-tags?with_count=true');

        $assetTags = $this->getStandardizedAssetTags();

        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=10&with_count=true"},
        "first": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=10&with_count=true"}
    },
    "current_page": 1,
    "items_count": 6,
    "_embedded": {
        "items": [
            ${assetTags['akeneo']},
            ${assetTags['animal']},
            ${assetTags['full_hd']},
            ${assetTags['popeye']},
            ${assetTags['thumbnail']},
            ${assetTags['view']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testIterationOnListTagsWithLimitAndWithoutCount()
    {
        $client = $this->createAuthenticatedClient();
        $assetTags = $this->getStandardizedAssetTags();

        $client->request('GET', '/api/rest/v1/asset-tags?limit=2&page=1');
        $response = $client->getResponse();
        $expectedPage1 = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=2&with_count=false"},
        "first": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=2&with_count=false"},
        "next": {"href": "http://localhost/api/rest/v1/asset-tags?page=2&limit=2&with_count=false"}
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            ${assetTags['akeneo']},
            ${assetTags['animal']}
        ]
    }
}
JSON;

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedPage1, $response->getContent());

        $client->request('GET', '/api/rest/v1/asset-tags?limit=2&page=2');
        $response = $client->getResponse();
        $expectedPage2 = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/asset-tags?page=2&limit=2&with_count=false"},
        "first": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=2&with_count=false"},
        "previous": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=2&with_count=false"},
        "next": {"href": "http://localhost/api/rest/v1/asset-tags?page=3&limit=2&with_count=false"}
    },
    "current_page": 2,
    "_embedded": {
        "items": [
            ${assetTags['full_hd']},
            ${assetTags['popeye']}
        ]
    }
}
JSON;

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedPage2, $response->getContent());

        $client->request('GET', '/api/rest/v1/asset-tags?limit=2&page=3');
        $response = $client->getResponse();
        $expectedPage3 = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/asset-tags?page=3&limit=2&with_count=false"},
        "first": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=2&with_count=false"},
        "previous": {"href": "http://localhost/api/rest/v1/asset-tags?page=2&limit=2&with_count=false"},
        "next": {"href": "http://localhost/api/rest/v1/asset-tags?page=4&limit=2&with_count=false"}
    },
    "current_page": 3,
    "_embedded": {
        "items": [
            ${assetTags['thumbnail']},
            ${assetTags['view']}
        ]
    }
}
JSON;

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedPage3, $response->getContent());

        $client->request('GET', '/api/rest/v1/asset-tags?limit=2&page=4');
        $response = $client->getResponse();
        $expectedPage4 = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/asset-tags?page=4&limit=2&with_count=false"},
        "first": {"href": "http://localhost/api/rest/v1/asset-tags?page=1&limit=2&with_count=false"},
        "previous": {"href": "http://localhost/api/rest/v1/asset-tags?page=3&limit=2&with_count=false"}
    },
    "current_page": 4,
    "_embedded": {
        "items": []
    }
}
JSON;

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedPage4, $response->getContent());
    }

    public function testListTagWithInvalidPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/asset-tags?pagination_type=michel');

        $response = $client->getResponse();
        $expected = <<<JSON
{
    "code": 422,
    "message": "Pagination type does not exist."
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
