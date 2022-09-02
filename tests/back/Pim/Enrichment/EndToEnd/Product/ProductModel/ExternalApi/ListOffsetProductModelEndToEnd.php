<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Response;

class ListOffsetProductModelEndToEnd extends AbstractProductModelTestCase
{
    /**
     * @group ce
     */
    public function testSuccessfullyGetListOfProductModelFirstPageWithCount()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/product-models?with_count=true&limit=3');

        $standardizedProducts = $this->getStandardizedProductModels();
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=3"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/product-models?page=2&with_count=true&pagination_type=page&limit=3"}
    },
    "current_page" : 1,
    "items_count"  : 6,
    "_embedded"    : {
        "items": [
            {$standardizedProducts['handbag']},
            {$standardizedProducts['hat']},
            {$standardizedProducts['shoes']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @group ce
     */
    public function testSuccessfullyGetListOfProductModelLastPageWithCount()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/product-models?with_count=true&limit=3&page=2');

        $standardizedProducts = $this->getStandardizedProductModels();
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?page=2&with_count=true&pagination_type=page&limit=3"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=3"},
        "previous" : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/product-models?page=3&with_count=true&pagination_type=page&limit=3"}
    },
    "current_page" : 2,
    "items_count"  : 6,
    "_embedded"    : {
        "items": [
            {$standardizedProducts['sweat']},
            {$standardizedProducts['trousers']},
            {$standardizedProducts['tshirt']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSuccessfullyGetListOfProductModelOutOfRange()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/product-models?with_count=true&page=2');

        $expected = <<<JSON
{
    "_links": {
        "self" : {"href" : "http://localhost/api/rest/v1/product-models?page=2&with_count=true&pagination_type=page&limit=10"},
        "first" : {"href" : "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=10"},
        "previous" : {"href" : "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=10"}
    },
    "current_page" : 2,
    "items_count"  : 6,
    "_embedded"    : {
        "items": []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/product-models?pagination_type=unknown');
        $response = $client->getResponse();

        $expected = sprintf(
            '{"code":%d,"message":"%s"}',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            addslashes('Pagination type does not exist.')
        );

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expected, $response->getContent());
    }

    public function testMaxPageWithOffsetPaginationType()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/product-models?page=101&limit=100');

        $message = addslashes('You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead');
        $expected = <<<JSON
{
    "code":422,
    "message":"${message}",
    "_links":{
        "documentation":{
            "href": "http:\/\/api.akeneo.com\/documentation\/pagination.html#the-search-after-method"
        }
    }
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $client->getResponse()->getContent());
    }

    public function testListSubProductModels()
    {
        $client = $this->createAuthenticatedClient();
        $search = '{"parent":[{"operator":"NOT EMPTY","value":null}]}';
        $client->request('GET', 'api/rest/v1/product-models?page=1&with_count=true&limit=10&search=' . $search);
        $encodedSearch = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self" : {"href" : "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=10&search=${encodedSearch}"},
        "first" : {"href" : "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=10&search=${encodedSearch}"}
    },
    "current_page" : 1,
    "items_count"  : 0,
    "_embedded"    : {
        "items": []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @group ce
     */
    public function testListRootProductModels()
    {
        $standardizedProductModels = $this->getStandardizedProductModels();

        $client = $this->createAuthenticatedClient();
        $search = '{"parent":[{"operator":"EMPTY","value":null}]}';
        $client->request('GET', 'api/rest/v1/product-models?page=1&with_count=true&limit=10&search=' . $search);
        $encodedSearch = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self" : {"href" : "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=10&search=${encodedSearch}"},
        "first" : {"href" : "http://localhost/api/rest/v1/product-models?page=1&with_count=true&pagination_type=page&limit=10&search=${encodedSearch}"}
    },
    "current_page" : 1,
    "items_count"  : 6,
    "_embedded"    : {
        "items": [
            {$standardizedProductModels['handbag']},
            {$standardizedProductModels['hat']},
            {$standardizedProductModels['shoes']},
            {$standardizedProductModels['sweat']},
            {$standardizedProductModels['trousers']},
            {$standardizedProductModels['tshirt']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testAccessDeniedWhenRetrievingProductModelsWithoutTheAcl()
    {
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_list');

        $client->request('GET', 'api/rest/v1/product-models');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}
