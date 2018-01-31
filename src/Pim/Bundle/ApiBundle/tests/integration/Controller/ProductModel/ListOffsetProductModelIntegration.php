<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Symfony\Component\HttpFoundation\Response;

class ListOffsetProductModelIntegration extends AbstractProductModelTestCase
{
    public function testSuccessfullyGetListOfProductModelWithoutParameter()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/product-models');

        $standardizedProducts = $this->getStandardizedProductModels();
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=10"}
    },
    "current_page" : 1,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['sweat']},
            {$standardizedProducts['shoes']},
            {$standardizedProducts['tshirt']},
            {$standardizedProducts['trousers']},
            {$standardizedProducts['hat']},
            {$standardizedProducts['handbag']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

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
            {$standardizedProducts['sweat']},
            {$standardizedProducts['shoes']},
            {$standardizedProducts['tshirt']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSuccessfullyGetListOfProductModelFirstPageWithoutCount()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/product-models?with_count=false&limit=3');

        $standardizedProducts = $this->getStandardizedProductModels();
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=3"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/product-models?page=2&with_count=false&pagination_type=page&limit=3"}
    },
    "current_page" : 1,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['sweat']},
            {$standardizedProducts['shoes']},
            {$standardizedProducts['tshirt']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

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
            {$standardizedProducts['trousers']},
            {$standardizedProducts['hat']},
            {$standardizedProducts['handbag']}
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

        $productModels = [];
        for ($i = 0; $i <= 10001; $i++) {
            $productModel = $this->get('pim_catalog.factory.product_model')->create();
            $this->get('pim_catalog.updater.product_model')
                ->update($productModel, [
                    'code' => 'prod-model-' . $i,
                    'family_variant' => 'familyVariantA1'
                ]);
            $productModels[] = $productModel;
        }

        $this->get('pim_catalog.saver.product_model')->saveAll($productModels);
        $this->get('akeneo_elasticsearch.client.product_model')->refreshIndex();

        $client->request('GET', 'api/rest/v1/product-models?page=101&limit=100');

        $message = addslashes('You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead');
        $expected = <<<JSON
{
    "code":422,
    "message":"${message}",
    "_links":{
        "documentation":{
            "href": "http:\/\/api.akeneo.com\/documentation\/pagination.html#search-after-type"
        }
    }
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $client->getResponse()->getContent());
    }
}
