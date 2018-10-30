<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeOption\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListAttributeOptionEndToEnd extends ApiTestCase
{
    public function testListAttributeOptions()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/a_simple_select/options');

        $expected =
<<<JSON
    {
        "_links": {
            "self": {
                "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options?page=1&limit=10&with_count=false"
            },
            "first": {
                "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options?page=1&limit=10&with_count=false"
            }
        },
        "current_page": 1,
        "_embedded": {
            "items": [
                {
                    "_links": {
                        "self": {
                            "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options/optionA"
                        }
                    },
                    "code": "optionA",
                    "attribute": "a_simple_select",
                    "sort_order": 10,
                    "labels": {"en_US": "Option A"}
                },
                {
                    "_links": {
                        "self": {
                            "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options/optionB"
                        }
                    },
                    "code": "optionB",
                    "attribute": "a_simple_select",
                    "sort_order": 20,
                    "labels": {"en_US": "Option B"}
                }
            ]
        }
    }
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
    public function testListAttributeOptionsWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/a_simple_select/options?with_count=true');

        $expected =
<<<JSON
    {
        "_links": {
            "self": {
                "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options?page=1&limit=10&with_count=true"
            },
            "first": {
                "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options?page=1&limit=10&with_count=true"
            }
        },
        "current_page": 1,
        "items_count": 2,
        "_embedded": {
            "items": [
                {
                    "_links": {
                        "self": {
                            "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options/optionA"
                        }
                    },
                    "code": "optionA",
                    "attribute": "a_simple_select",
                    "sort_order": 10,
                    "labels": {"en_US": "Option A"}
                },
                {
                    "_links": {
                        "self": {
                            "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options/optionB"
                        }
                    },
                    "code": "optionB",
                    "attribute": "a_simple_select",
                    "sort_order": 20,
                    "labels": {"en_US": "Option B"}
                }
            ]
        }
    }
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListAttributeOptions()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/a_simple_select/options?limit=10&page=2');

        $expected =
<<<JSON
    {
        "_links": {
            "self": {
                "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options?page=2&limit=10&with_count=false"
            },
            "first": {
                "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options?page=1&limit=10&with_count=false"
            },
            "previous": {
                "href": "http://localhost/api/rest/v1/attributes/a_simple_select/options?page=1&limit=10&with_count=false"
            }
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

    public function testAttributeNotFound()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/not_found/options');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Attribute "not_found" does not exist.', $content['message']);
    }

    public function testAttributeNotSupportingOptions()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/sku/options');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame(
            'Attribute "sku" does not support options. Only attributes of type "pim_catalog_simpleselect", "pim_catalog_multiselect" support options.',
            $content['message']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
