<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Family\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListFamilyEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     * TODO: to test with pagination
     */
    public function testListFamilies()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families');

        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/families?page=1&limit=10&with_count=false"},
        "first": {"href": "http://localhost/api/rest/v1/families?page=1&limit=10&with_count=false"}
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/families/familyA"
                    }
                },
                "code": "familyA",
                "attributes": [
                    "a_date", "a_file", "a_localizable_image", "a_localized_and_scopable_text_area", "a_metric",
                    "a_multi_select", "a_number_float", "a_number_float_negative", "a_number_integer", "a_price",
                    "a_ref_data_multi_select", "a_ref_data_simple_select", "a_scopable_price", "a_simple_select",
                    "a_text", "a_text_area", "a_yes_no", "an_image", "sku"
                ],
                "attribute_as_label": "sku",
                "attribute_as_image": "an_image",
                "attribute_requirements": {
                    "ecommerce": [
                        "a_date", "a_file", "a_localizable_image", "a_localized_and_scopable_text_area", "a_metric",
                        "a_multi_select", "a_number_float", "a_number_float_negative", "a_number_integer", "a_price",
                        "a_ref_data_multi_select", "a_ref_data_simple_select", "a_scopable_price", "a_simple_select",
                        "a_text", "a_text_area", "a_yes_no", "an_image", "sku"
                    ],
                    "ecommerce_china" : ["sku"],
                    "tablet": [
                        "a_date", "a_file", "a_localizable_image", "a_localized_and_scopable_text_area", "a_metric",
                        "a_multi_select", "a_number_float", "a_number_float_negative", "a_number_integer", "a_price",
                        "a_ref_data_multi_select", "a_ref_data_simple_select", "a_scopable_price", "a_simple_select",
                        "a_text", "a_text_area", "a_yes_no", "an_image", "sku"
                    ]
                },
                "labels": {
                    "fr_FR" : "Une famille A",
                    "en_US" : "A family A"
                }
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/families/familyA1"
                    }
                },
                "code": "familyA1",
                "attributes": ["a_date", "a_file", "a_localizable_image", "sku"],
                "attribute_as_label": "sku",
                "attribute_as_image": null,
                "attribute_requirements": {
                    "ecommerce": ["a_date", "a_file", "sku"],
                    "ecommerce_china": ["sku"],
                    "tablet": ["a_file", "a_localizable_image", "sku"]
                },
                "labels": {
                    "en_US" : "A family A1"
                }
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/families/familyA2"
                    }
                },
                "code": "familyA2",
                "attributes": ["a_metric", "a_number_float", "sku"],
                "attribute_as_label": "sku",
                "attribute_as_image": null,
                "attribute_requirements" : {
                    "ecommerce": ["a_metric", "sku"],
                    "ecommerce_china": ["sku"],
                    "tablet": ["a_number_float", "sku"]
                },
                "labels": {}
            },
            {
            "_links":{
               "self":{
                  "href":"http:\/\/localhost\/api\/rest\/v1\/families\/familyA3"
               }
            },
            "code":"familyA3",
            "attributes":[
               "a_localized_and_scopable_text_area",
               "a_simple_select",
               "a_text",
               "a_yes_no",
               "sku"
            ],
            "attribute_as_label":"sku",
            "attribute_as_image":null,
            "attribute_requirements":{
               "ecommerce":[
                  "a_localized_and_scopable_text_area",
                  "a_simple_select",
                  "a_yes_no",
                  "sku"
               ],
               "ecommerce_china":[
                  "sku"
               ],
               "tablet":[
                  "a_localized_and_scopable_text_area",
                  "a_simple_select",
                  "a_yes_no",
                  "sku"
               ]
            },
            "labels":{
            
            }
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListFamiliesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families?limit=10&page=2&with_count=true');

        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/families?page=2&limit=10&with_count=true"},
        "first": {"href": "http://localhost/api/rest/v1/families?page=1&limit=10&with_count=true"},
        "previous": {"href": "http://localhost/api/rest/v1/families?page=1&limit=10&with_count=true"}
    },
    "current_page": 2,
    "items_count": 4,
    "_embedded": {
        "items": []
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListFamilies()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/families?limit=10&page=2');

        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/families?page=2&limit=10&with_count=false"},
        "first": {"href": "http://localhost/api/rest/v1/families?page=1&limit=10&with_count=false"},
        "previous": {"href": "http://localhost/api/rest/v1/families?page=1&limit=10&with_count=false"}
    },
    "current_page": 2,
    "_embedded": {
        "items": []
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
