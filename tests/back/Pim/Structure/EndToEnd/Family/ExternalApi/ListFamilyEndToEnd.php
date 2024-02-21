<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Family\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

class ListFamilyEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     */
    public function testListFamiliesWithPagination(): void
    {
        $standardizedFamilies = $this->getStandardizedFamilies();

        $firstPage = $this->createAuthenticatedClient();
        $firstPage->request('GET', 'api/rest/v1/families?page=1&limit=2&with_count=true');
        $secondPage = $this->createAuthenticatedClient();
        $secondPage->request('GET', 'api/rest/v1/families?page=2&limit=2&with_count=true');

        $expectedFirstPage = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/families?page=1&limit=2&with_count=true"},
        "first": {"href": "http://localhost/api/rest/v1/families?page=1&limit=2&with_count=true"},
        "next": {"href": "http://localhost/api/rest/v1/families?page=2&limit=2&with_count=true"}
    },
    "current_page": 1,
    "items_count": 4,
    "_embedded": {
        "items": [
            {$standardizedFamilies['familyA']},
            {$standardizedFamilies['familyA1']}
        ]
    }
}
JSON;
        $expectedSecondPage = <<<JSON
{
    "_links": {
        "first": {"href": "http://localhost/api/rest/v1/families?page=1&limit=2&with_count=true"},
        "next": {"href": "http://localhost/api/rest/v1/families?page=3&limit=2&with_count=true"},
        "previous": {"href": "http://localhost/api/rest/v1/families?page=1&limit=2&with_count=true"},
        "self": {"href": "http://localhost/api/rest/v1/families?page=2&limit=2&with_count=true"}
    },
    "current_page": 2,
    "items_count": 4,
    "_embedded": {
        "items": [
            {$standardizedFamilies['familyA2']},
            {$standardizedFamilies['familyA3']}
        ]
    }
}
JSON;

        $firstPageResponse = $firstPage->getResponse();
        $this->assertSame(Response::HTTP_OK, $firstPageResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedFirstPage, $firstPageResponse->getContent());

        $secondPageResponse = $secondPage->getResponse();
        $this->assertSame(Response::HTTP_OK, $secondPageResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedSecondPage, $secondPageResponse->getContent());
    }

    public function testListFamiliesByCodes(): void
    {
        $standardizedFamilies = $this->getStandardizedFamilies();
        $client = $this->createAuthenticatedClient();
        $search = '{"code":[{"operator":"IN","value":["familyA","familyA1","familyA2"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/families?limit=2&page=1&with_count=true&search=' . $search);

        $expected = <<<JSON
{
    "_links": {
        "first": {"href": "http://localhost/api/rest/v1/families?page=1&limit=2&with_count=true&search={$searchEncoded}"},
        "next": {"href": "http://localhost/api/rest/v1/families?page=2&limit=2&with_count=true&search={$searchEncoded}"},
        "self": {"href": "http://localhost/api/rest/v1/families?page=1&limit=2&with_count=true&search={$searchEncoded}"}
    },
    "current_page": 1,
    "items_count": 3,
    "_embedded": {
        "items": [
            {$standardizedFamilies['familyA']},
            {$standardizedFamilies['familyA1']}
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListFamiliesByUpdated()
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $affected = $connection->exec('UPDATE pim_catalog_family SET updated="2019-05-15 16:27:00" WHERE code IN ("familyA","familyA1")');
        $this->assertEquals(2, $affected, 'There is more result as expected during test setup, the test will not work.');

        $client = $this->createAuthenticatedClient();
        $search = '{"updated":[{"operator":">","value":"2020-01-01T10:00:01Z"}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/families?limit=5&with_count=true&search=' . $search);

        $standardizedFamilies = $this->getStandardizedFamilies();
        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/families?page=1&limit=5&with_count=true&search={$searchEncoded}"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/families?page=1&limit=5&with_count=true&search={$searchEncoded}"
		}
	},
	"current_page": 1,
	"items_count": 2,
    "_embedded" : {
        "items" : [
            {$standardizedFamilies['familyA2']},
            {$standardizedFamilies['familyA3']}
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }


    public function testListFamiliesWithSeveralSearchFilters()
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $affected = $connection->exec('UPDATE pim_catalog_family SET updated="2019-05-15 16:27:00" WHERE code IN ("familyA")');
        $this->assertEquals(1, $affected, 'There is more result as expected during test setup, the test will not work.');

        $client = $this->createAuthenticatedClient();
        $search = '{"updated":[{"operator":">","value":"2020-01-01T10:00:01Z"}],"code":[{"operator":"IN","value":["familyA","familyA1","familyA2"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/families?limit=5&with_count=true&search=' . $search);

        $standardizedFamilies = $this->getStandardizedFamilies();
        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/families?page=1&limit=5&with_count=true&search={$searchEncoded}"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/families?page=1&limit=5&with_count=true&search={$searchEncoded}"
		}
	},
	"current_page": 1,
	"items_count": 2,
    "_embedded" : {
        "items" : [
            {$standardizedFamilies['familyA1']},
            {$standardizedFamilies['familyA2']}
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

    protected function getStandardizedFamilies(): array
    {
        $families['familyA'] = <<<JSON
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
}
JSON;
        $families['familyA1'] = <<<JSON
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
}
JSON;
        $families['familyA2'] = <<<JSON
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
}
JSON;
        $families['familyA3'] = <<<JSON
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
    "labels":{}
}
JSON;

        return $families;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
