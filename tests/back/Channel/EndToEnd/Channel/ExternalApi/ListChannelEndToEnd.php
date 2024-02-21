<?php

namespace AkeneoTest\Channel\EndToEnd\Channel\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListChannelEndToEnd extends ApiTestCase
{
    public function testListChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels');

        $apiChannels = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=10&with_count=false"},
        "first" : {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=10&with_count=false"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/channels/ecommerce"}
                },
                "code"             : "ecommerce",
                "currencies"       : ["USD", "EUR"],
                "locales"          : ["en_US"],
                "category_tree"    : "master",
                "conversion_units" : {
                    "a_metric_without_decimal": "METER",
                    "a_metric": "KILOWATT"
                },
                "labels"           : {
                    "en_US" : "Ecommerce",
                    "fr_FR" : "Ecommerce"
                }
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/channels/ecommerce_china"}
                },
                "code"             : "ecommerce_china",
                "currencies"       : ["CNY"],
                "locales"          : ["en_US", "zh_CN"],
                "category_tree"    : "master_china",
                "conversion_units" : {},
                "labels"           : {
                    "en_US" : "Ecommerce china",
                    "fr_FR" : "Ecommerce chine"
                }
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/channels/tablet"}
                },
                "code"             : "tablet",
                "currencies"       : ["USD", "EUR"],
                "locales"          : ["de_DE", "en_US", "fr_FR"],
                "category_tree"    : "master",
                "conversion_units" : {},
                "labels"           : {
                    "en_US" : "Tablet",
                    "fr_FR" : "Tablette"
                }
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($apiChannels, $response->getContent());
    }

    public function testListChannelsWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels?with_count=true');

        $apiChannels = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=10&with_count=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=10&with_count=true"}
    },
    "current_page" : 1,
    "items_count"  : 3,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/channels/ecommerce"}
                },
                "code"             : "ecommerce",
                "currencies"       : ["USD", "EUR"],
                "locales"          : ["en_US"],
                "category_tree"    : "master",
                "conversion_units" : {
                    "a_metric_without_decimal": "METER",
                    "a_metric": "KILOWATT"
                },
                "labels"           : {
                    "en_US" : "Ecommerce",
                    "fr_FR" : "Ecommerce"
                }
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/channels/ecommerce_china"}
                },
                "code"             : "ecommerce_china",
                "currencies"       : ["CNY"],
                "locales"          : ["en_US", "zh_CN"],
                "category_tree"    : "master_china",
                "conversion_units" : {},
                "labels"           : {
                    "en_US" : "Ecommerce china",
                    "fr_FR" : "Ecommerce chine"
                }
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/channels/tablet"}
                },
                "code"             : "tablet",
                "currencies"       : ["USD", "EUR"],
                "locales"          : ["de_DE", "en_US", "fr_FR"],
                "category_tree"    : "master",
                "conversion_units" : {},
                "labels"           : {
                    "en_US" : "Tablet",
                    "fr_FR" : "Tablette"
                }
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($apiChannels, $response->getContent());
    }

    public function testOutOfRangeListChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels?page=2&limit=5');

        $apiChannels = <<<JSON
{
    "_links"       : {
        "self": {"href" : "http://localhost/api/rest/v1/channels?page=2&limit=5&with_count=false"},
        "first": {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=5&with_count=false"},
        "previous": {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=5&with_count=false"}
    },
    "current_page" : 2,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($apiChannels, $response->getContent());
    }

    public function testPaginationListOfChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels?page=2&limit=2');

        $apiChannels = <<<JSON
{
    "_links"       : {
        "self": {"href" : "http://localhost/api/rest/v1/channels?page=2&limit=2&with_count=false"},
        "first": {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=2&with_count=false"},
        "previous": {"href" : "http://localhost/api/rest/v1/channels?page=1&limit=2&with_count=false"}
    },
    "current_page" : 2,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/channels/tablet"}
                },
                "code"             : "tablet",
                "currencies"       : ["USD", "EUR"],
                "locales"          : ["de_DE", "en_US", "fr_FR"],
                "category_tree"    : "master",
                "conversion_units" : {},
                "labels"           : {
                    "en_US" : "Tablet",
                    "fr_FR" : "Tablette"
                }
            }
        ]
    }
}
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($apiChannels, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
