<?php

namespace AkeneoTest\Channel\EndToEnd\Currency\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListCurrencyEndToEnd extends ApiTestCase
{
    public function testListCurrencies()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/currencies');

        $standardizedCurrencies = $this->getStandardizedCurrencies();

        $expected = <<<JSON
{
    "_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/currencies?page=1&limit=10&with_count=false"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/currencies?page=2&limit=10&with_count=false"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/currencies?page=1&limit=10&with_count=false"
		}
	},
    "current_page": 1,
    "_embedded" : {
        "items" : [
            {$standardizedCurrencies['ADP']},
            {$standardizedCurrencies['AED']},
            {$standardizedCurrencies['AFA']},
            {$standardizedCurrencies['ALK']},
            {$standardizedCurrencies['AOK']},
            {$standardizedCurrencies['AON']},
            {$standardizedCurrencies['AOR']},
            {$standardizedCurrencies['ARL']},
            {$standardizedCurrencies['ARM']},
            {$standardizedCurrencies['ARP']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testCurrenciesWithLimitAndPage()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/currencies?limit=5&page=2&with_count=false');

        $standardizedCurrencies = $this->getStandardizedCurrencies();

        $expected = <<<JSON
{
    "_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/currencies?page=2&limit=5&with_count=false"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/currencies?page=1&limit=5&with_count=false"
		},
		"previous": {
			"href": "http://localhost/api/rest/v1/currencies?page=1&limit=5&with_count=false"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/currencies?page=3&limit=5&with_count=false"
		}
	},
    "current_page": 2,
    "_embedded" : {
        "items" : [
            {$standardizedCurrencies['AON']},
            {$standardizedCurrencies['AOR']},
            {$standardizedCurrencies['ARL']},
            {$standardizedCurrencies['ARM']},
            {$standardizedCurrencies['ARP']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testCurrenciesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/currencies?limit=5&page=2&with_count=true');

        $standardizedCurrencies = $this->getStandardizedCurrencies();

        $expected = <<<JSON
{
        "_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/currencies?page=2&limit=5&with_count=true"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/currencies?page=1&limit=5&with_count=true"
		},
		"previous": {
			"href": "http://localhost/api/rest/v1/currencies?page=1&limit=5&with_count=true"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/currencies?page=3&limit=5&with_count=true"
		}
	},
    "current_page": 2,
    "items_count": 102,
    "_embedded" : {
        "items" : [
            {$standardizedCurrencies['AON']},
            {$standardizedCurrencies['AOR']},
            {$standardizedCurrencies['ARL']},
            {$standardizedCurrencies['ARM']},
            {$standardizedCurrencies['ARP']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListCurrencies()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/currencies?limit=100&page=3');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies?page=3&limit=100&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/currencies?page=1&limit=100&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/currencies?page=2&limit=100&with_count=false"
        }
    },
    "current_page": 3,
    "_embedded": {
        "items": []
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

        $client->request('GET', 'api/rest/v1/currencies?pagination_type=unknown');

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

        $client->request('GET', 'api/rest/v1/currencies?pagination_type=search_after');

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
    protected function getStandardizedCurrencies()
    {
        $standarizedCurrencies['ADP'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/ADP"
        }
    },
    "code": "ADP",
    "enabled": false
}
JSON;

        $standarizedCurrencies['AED'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/AED"
        }
    },
    "code": "AED",
    "enabled": false
}
JSON;

        $standarizedCurrencies['AFA'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/AFA"
        }
    },
    "code": "AFA",
    "enabled": false
}
JSON;

        $standarizedCurrencies['ALK'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/ALK"
        }
    },
    "code": "ALK",
    "enabled": false
}
JSON;

        $standarizedCurrencies['AOK'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/AOK"
        }
    },
    "code": "AOK",
    "enabled": false
}
JSON;

        $standarizedCurrencies['AON'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/AON"
        }
    },
    "code": "AON",
    "enabled": false
}
JSON;

        $standarizedCurrencies['AOR'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/AOR"
        }
    },
    "code": "AOR",
    "enabled": false
}
JSON;

        $standarizedCurrencies['ARL'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/ARL"
        }
    },
    "code": "ARL",
    "enabled": false
}
JSON;

        $standarizedCurrencies['ARM'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/ARM"
        }
    },
    "code": "ARM",
    "enabled": false
}
JSON;

        $standarizedCurrencies['ARP'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/currencies/ARP"
        }
    },
    "code": "ARP",
    "enabled": false
}
JSON;

        return $standarizedCurrencies;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
