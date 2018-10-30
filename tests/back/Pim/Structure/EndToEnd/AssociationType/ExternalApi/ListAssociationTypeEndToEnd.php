<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AssociationType\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListAssociationTypeEndToEnd extends ApiTestCase
{
    public function testListAssociationTypes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types');

        $standardizedAssociationTypes = $this->getStandardizedAssociationTypes();

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=10&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=10&with_count=false"
        }
    },
    "current_page": 1,
    "_embedded" : {
        "items" : [
            {$standardizedAssociationTypes['PACK']},
            {$standardizedAssociationTypes['SUBSTITUTION']},
            {$standardizedAssociationTypes['UPSELL']},
            {$standardizedAssociationTypes['X_SELL']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListAssociationTypesWithLimitAndPage()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types?limit=1&page=2');

        $standardizedAssociationTypes = $this->getStandardizedAssociationTypes();

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/association-types?page=2&limit=1&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=1&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=1&with_count=false"
        },
        "next": {
            "href": "http://localhost/api/rest/v1/association-types?page=3&limit=1&with_count=false"
        }
    },
    "current_page": 2,
    "_embedded" : {
        "items" : [
            {$standardizedAssociationTypes['SUBSTITUTION']}
        ]
    }
}
JSON;
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListAssociationTypesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types?with_count=true');

        $standardizedAssociationTypes = $this->getStandardizedAssociationTypes();

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=10&with_count=true"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=10&with_count=true"
        }
    },
    "current_page": 1,
    "items_count": 4,
    "_embedded" : {
        "items" : [
            {$standardizedAssociationTypes['PACK']},
            {$standardizedAssociationTypes['SUBSTITUTION']},
            {$standardizedAssociationTypes['UPSELL']},
            {$standardizedAssociationTypes['X_SELL']}
        ]
    }
}
JSON;
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListAssociationTypes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types?limit=100&page=2');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/association-types?page=2&limit=100&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=100&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/association-types?page=1&limit=100&with_count=false"
        }
    },
    "current_page": 2,
    "_embedded" : {
        "items" : []
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types?pagination_type=unknown');

        $expected = <<<JSON
{
	"code": 422,
	"message": "Pagination type does not exist."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testUnsupportedPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types?pagination_type=search_after');

        $expected = <<<JSON
{
	"code": 422,
	"message": "Pagination type is not supported."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @return array
     */
    protected function getStandardizedAssociationTypes()
    {
        $standardizedAssociationTypes['X_SELL'] = <<<JSON
{
    "_links":{  
       "self":{  
          "href":"http://localhost/api/rest/v1/association-types/X_SELL"
       }
    },
    "code":"X_SELL",
    "labels":{
        "en_US": "Cross sell",
        "fr_FR": "Vente croisée"
    }
}
JSON;

        $standardizedAssociationTypes['UPSELL'] = <<<JSON
{
    "_links":{  
       "self":{  
          "href":"http://localhost/api/rest/v1/association-types/UPSELL"
       }
    },
    "code":"UPSELL",
    "labels":{
        "en_US": "Upsell",
        "fr_FR": "Vente incitative"
    }
}
JSON;

        $standardizedAssociationTypes['SUBSTITUTION'] = <<<JSON
{
    "_links":{  
       "self":{  
          "href":"http://localhost/api/rest/v1/association-types/SUBSTITUTION"
       }
    },
    "code":"SUBSTITUTION",
    "labels":{
        "en_US": "Substitution",
        "fr_FR": "Remplacement"
    }
}
JSON;

        $standardizedAssociationTypes['PACK'] = <<<JSON
{
    "_links":{  
       "self":{  
          "href":"http://localhost/api/rest/v1/association-types/PACK"
       }
    },
    "code":"PACK",
    "labels":{
        "en_US": "Pack",
        "fr_FR": "Pack"
    }
}
JSON;

        return $standardizedAssociationTypes;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
