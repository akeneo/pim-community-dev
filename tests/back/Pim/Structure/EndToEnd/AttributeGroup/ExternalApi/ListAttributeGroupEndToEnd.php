<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeGroup\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListAttributeGroupEndToEnd extends ApiTestCase
{
    public function testListAttributeGroups()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attribute-groups');

        $standardizedAttributeGroups = $this->getStandardizedAttributeGroups();

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=1&limit=10&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=1&limit=10&with_count=false"
        }
    },
    "current_page": 1,
    "_embedded" : {
        "items" : [
            {$standardizedAttributeGroups['attributeGroupA']},
            {$standardizedAttributeGroups['attributeGroupB']},
            {$standardizedAttributeGroups['attributeGroupC']},
            {$standardizedAttributeGroups['other']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListAttributeGroupsWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attribute-groups?limit=1&page=2&with_count=true');

        $standardizedAttributeGroups = $this->getStandardizedAttributeGroups();

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=2&limit=1&with_count=true"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=1&limit=1&with_count=true"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=1&limit=1&with_count=true"
        },
        "next": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=3&limit=1&with_count=true"
        }
    },
    "current_page": 2,
    "items_count": 4,
    "_embedded" : {
        "items" : [
            {$standardizedAttributeGroups['attributeGroupB']}
        ]
    }
}
JSON;
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListAttributeGroups()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attribute-groups?limit=100&page=2');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=2&limit=100&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=1&limit=100&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/attribute-groups?page=1&limit=100&with_count=false"
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

        $client->request('GET', 'api/rest/v1/attribute-groups?pagination_type=unknown');

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

        $client->request('GET', 'api/rest/v1/attribute-groups?pagination_type=search_after');

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
    protected function getStandardizedAttributeGroups()
    {
        $standardizedAttributeGroups['attributeGroupA'] = <<<JSON
{
    "_links":{
       "self":{
          "href":"http://localhost/api/rest/v1/attribute-groups/attributeGroupA"
       }
    },
    "code":"attributeGroupA",
    "sort_order":1,
    "attributes":[
       "sku",
       "a_date",
       "a_file",
       "an_image",
       "a_price",
       "a_price_without_decimal",
       "a_ref_data_multi_select",
       "a_ref_data_simple_select",
       "a_text",
       "a_regexp",
       "a_text_area",
       "a_yes_no",
       "a_scopable_price",
       "a_localized_and_scopable_text_area"
    ],
    "labels":{
       "en_US":"Attribute group A",
       "fr_FR":"Groupe d\u0027attribut A"
    }
}
JSON;

        $standardizedAttributeGroups['attributeGroupB'] = <<<JSON
{
    "_links":{
       "self":{
          "href":"http://localhost/api/rest/v1/attribute-groups/attributeGroupB"
       }
    },
    "code":"attributeGroupB",
    "sort_order":2,
    "attributes":[
       "a_metric",
       "a_metric_without_decimal",
       "a_metric_negative",
       "a_number_float",
       "a_number_float_negative",
       "a_number_integer",
       "a_number_integer_negative",
       "a_simple_select",
       "a_localizable_image",
       "a_scopable_image",
       "a_localizable_scopable_image",
       "a_simple_select_color",
       "a_simple_select_size"
    ],
    "labels":{
       "en_US":"Attribute group B",
       "fr_FR":"Groupe d\u0027attribut B"
    }
}
JSON;

    $standardizedAttributeGroups['attributeGroupC'] = <<<JSON
{
    "_links":{
       "self":{
          "href":"http://localhost/api/rest/v1/attribute-groups/attributeGroupC"
       }
    },
    "code":"attributeGroupC",
    "sort_order":3,
    "attributes":[
       "a_metric_without_decimal_negative",
       "a_multi_select"
    ],
    "labels":{
       "en_US":"Attribute group C",
       "fr_FR":"Groupe d\u0027attribut C"
    }
}
JSON;

        $standardizedAttributeGroups['other'] = <<<JSON
{
    "_links":{
       "self":{
          "href":"http://localhost/api/rest/v1/attribute-groups/other"
       }
    },
    "code":"other",
    "sort_order":100,
    "attributes":[],
    "labels":{
       "en_US":"Other",
       "fr_FR":"Autre"
    }
}
JSON;

        return $standardizedAttributeGroups;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
