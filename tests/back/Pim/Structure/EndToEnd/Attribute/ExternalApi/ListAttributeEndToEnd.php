<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class ListAttributeEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     */
    public function testAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes');

        $standardizedAttributes = $this->getStandardizedAttributes();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=10&with_count=false"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=10&with_count=false"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/attributes?page=2&limit=10&with_count=false"
		}
	},
	"current_page": 1,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['a_date']},
            {$standardizedAttributes['a_file']},
            {$standardizedAttributes['a_localizable_image']},
            {$standardizedAttributes['a_localizable_scopable_image']},
            {$standardizedAttributes['a_localized_and_scopable_text_area']},
            {$standardizedAttributes['a_metric']},
            {$standardizedAttributes['a_metric_negative']},
            {$standardizedAttributes['a_metric_without_decimal']},
            {$standardizedAttributes['a_metric_without_decimal_negative']},
            {$standardizedAttributes['a_multi_select']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @group critical
     * TODO: to merge with the first one to test the pagination
     */
    public function testAttributesWithLimitAndPage()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes?limit=5&page=2&with_count=false');

        $standardizedAttributes = $this->getStandardizedAttributes();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=2&limit=5&with_count=false"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=false"
		},
		"previous": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=false"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/attributes?page=3&limit=5&with_count=false"
		}
	},
	"current_page": 2,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['a_metric']},
            {$standardizedAttributes['a_metric_negative']},
            {$standardizedAttributes['a_metric_without_decimal']},
            {$standardizedAttributes['a_metric_without_decimal_negative']},
            {$standardizedAttributes['a_multi_select']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeSearchByCode()
    {
        $client = $this->createAuthenticatedClient();
        $search = '{"code":[{"operator":"IN","value":["a_metric","a_multi_select", "a_metric_negative"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/attributes?limit=2&page=1&with_count=true&search=' . $search);

        $standardizedAttributes = $this->getStandardizedAttributes();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=2&with_count=true&search={$searchEncoded}"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=2&with_count=true&search={$searchEncoded}"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/attributes?page=2&limit=2&with_count=true&search={$searchEncoded}"
		}
	},
	"current_page": 1,
	"items_count": 3,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['a_metric']},
            {$standardizedAttributes['a_metric_negative']}
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeSearchByUpdated()
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $affected = $connection->exec('UPDATE pim_catalog_attribute SET updated="2019-05-15 16:27:00" WHERE code="a_file"');
        $this->assertEquals(1, $affected, 'There is more result as expected during test setup, the test will not work.');

        $client = $this->createAuthenticatedClient();
        $search = '{"updated":[{"operator":">","value":"2020-01-01T10:00:01Z"}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/attributes?limit=5&with_count=true&search=' . $search);

        $standardizedAttributes = $this->getStandardizedAttributes();
        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true&search={$searchEncoded}"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true&search={$searchEncoded}"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/attributes?page=2&limit=5&with_count=true&search={$searchEncoded}"
		}
	},
	"current_page": 1,
	"items_count": 29,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['a_date']},
            {$standardizedAttributes['a_localizable_image']},
            {$standardizedAttributes['a_localizable_scopable_image']},
            {$standardizedAttributes['a_localized_and_scopable_text_area']},
            {$standardizedAttributes['a_metric']}
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes?limit=5&page=2&with_count=true');

        $standardizedAttributes = $this->getStandardizedAttributes();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=2&limit=5&with_count=true"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true"
		},
		"previous": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/attributes?page=3&limit=5&with_count=true"
		}
	},
	"current_page": 2,
	"items_count": 30,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['a_metric']},
            {$standardizedAttributes['a_metric_negative']},
            {$standardizedAttributes['a_metric_without_decimal']},
            {$standardizedAttributes['a_metric_without_decimal_negative']},
            {$standardizedAttributes['a_multi_select']}
        ]
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeSearchByCodes()
    {
        $client = $this->createAuthenticatedClient();
        $search = '{"code":[{"operator":"IN","value":["a_metric","a_multi_select", "a_metric_negative"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/attributes?limit=2&page=1&with_count=true&search=' . $search);

        $standardizedAttributes = $this->getStandardizedAttributes();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=2&with_count=true&search={$searchEncoded}"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=2&with_count=true&search={$searchEncoded}"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/attributes?page=2&limit=2&with_count=true&search={$searchEncoded}"
		}
	},
	"current_page": 1,
	"items_count": 3,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['a_metric']},
            {$standardizedAttributes['a_metric_negative']}
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeSearchByTypes()
    {
        $client = $this->createAuthenticatedClient();
        $search = '{"type":[{"operator":"IN","value":["pim_catalog_metric", "pim_catalog_date", "pim_catalog_file"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/attributes?limit=5&page=1&with_count=true&search=' . $search);

        $standardizedAttributes = $this->getStandardizedAttributes();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true&search={$searchEncoded}"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true&search={$searchEncoded}"
		},
		"next": {
			"href": "http://localhost/api/rest/v1/attributes?page=2&limit=5&with_count=true&search={$searchEncoded}"
		}
	},
	"current_page": 1,
	"items_count": 6,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['a_date']},
            {$standardizedAttributes['a_file']},
            {$standardizedAttributes['a_metric']},
            {$standardizedAttributes['a_metric_negative']},
            {$standardizedAttributes['a_metric_without_decimal']}
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeSearchByMainIdentifier()
    {
        $client = $this->createAuthenticatedClient();
        $search = '{"is_main_identifier":[{"operator":"=","value":true}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);

        $client->request('GET', 'api/rest/v1/attributes?limit=5&page=1&with_count=true&search=' . $search);

        $standardizedAttributes = $this->getStandardizedAttributes();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true&search={$searchEncoded}"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/attributes?page=1&limit=5&with_count=true&search={$searchEncoded}"
		}
	},
	"current_page": 1,
	"items_count": 1,
    "_embedded" : {
        "items" : [
            {$standardizedAttributes['sku']}
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOutOfRangeListAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes?limit=100&page=2');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes?page=2&limit=100&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/attributes?page=1&limit=100&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/attributes?page=1&limit=100&with_count=false"
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

    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes?pagination_type=unknown');

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

        $client->request('GET', 'api/rest/v1/attributes?pagination_type=search_after');

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
    protected function getStandardizedAttributes()
    {
        $standardizedAttributes['sku'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/sku"
        }
    },
    "code": "sku",
    "type": "pim_catalog_identifier",
    "group": "attributeGroupA",
    "unique": true,
    "useable_as_grid_filter": true,
    "allowed_extensions": [],
    "metric_family": null,
    "default_metric_unit": null,
    "reference_data_name": null,
    "available_locales": [],
    "max_characters": null,
    "validation_rule": null,
    "validation_regexp": null,
    "wysiwyg_enabled": null,
    "number_min": null,
    "number_max": null,
    "decimals_allowed": null,
    "negative_allowed": null,
    "date_min": null,
    "date_max": null,
    "max_file_size": null,
    "minimum_input_length": null,
    "sort_order": 0,
    "localizable": false,
    "scopable": false,
    "labels": {},
    "guidelines": {"en_US": "this is the sku"},
    "auto_option_sorting": null,
    "default_value": null,
    "group_labels"           : {"en_US": "Attribute group A","fr_FR": "Groupe d'attribut A"},
    "is_main_identifier": true
}
JSON;

        $standardizedAttributes['a_date'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_date"
        }
    },
    "code"                   : "a_date",
    "type"                   : "pim_catalog_date",
    "group"                  : "attributeGroupA",
    "group_labels"           : {"en_US": "Attribute group A","fr_FR": "Groupe d'attribut A"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : null,
    "default_metric_unit"    : null,
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : null,
    "negative_allowed"       : null,
    "date_min"               : "2005-05-25T00:00:00+02:00",
    "date_max"               : "2050-12-31T00:00:00+01:00",
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 2,
    "localizable"            : false,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {"en_US": "date guidelines"},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_file'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_file"
        }
    },
    "code"                   : "a_file",
    "type"                   : "pim_catalog_file",
    "group"                  : "attributeGroupA",
    "group_labels"           : {"en_US": "Attribute group A","fr_FR": "Groupe d'attribut A"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : ["pdf", "doc", "docx", "txt"],
    "metric_family"          : null,
    "default_metric_unit"    : null,
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : null,
    "negative_allowed"       : null,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 1,
    "localizable"            : false,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_localizable_image'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_localizable_image"
        }
    },
    "code"                   : "a_localizable_image",
    "type"                   : "pim_catalog_image",
    "group"                  : "attributeGroupB",
    "group_labels"           : {"en_US": "Attribute group B","fr_FR": "Groupe d'attribut B"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : null,
    "default_metric_unit"    : null,
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : null,
    "negative_allowed"       : null,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 0,
    "localizable"            : true,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_localizable_scopable_image'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_localizable_scopable_image"
        }
    },
    "code"                   : "a_localizable_scopable_image",
    "type"                   : "pim_catalog_image",
    "group"                  : "attributeGroupB",
    "group_labels"           : {"en_US": "Attribute group B","fr_FR": "Groupe d'attribut B"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : null,
    "default_metric_unit"    : null,
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : null,
    "negative_allowed"       : null,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 0,
    "localizable"            : true,
    "scopable"               : true,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_localized_and_scopable_text_area'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_localized_and_scopable_text_area"
        }
    },
    "code"                   : "a_localized_and_scopable_text_area",
    "type"                   : "pim_catalog_textarea",
    "group"                  : "attributeGroupA",
    "group_labels"           : {"en_US": "Attribute group A","fr_FR": "Groupe d'attribut A"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : null,
    "default_metric_unit"    : null,
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : false,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : null,
    "negative_allowed"       : null,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 10,
    "localizable"            : true,
    "scopable"               : true,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_metric'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_metric"
        }
    },
    "code"                   : "a_metric",
    "type"                   : "pim_catalog_metric",
    "group"                  : "attributeGroupB",
    "group_labels"           : {"en_US": "Attribute group B","fr_FR": "Groupe d'attribut B"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : "Power",
    "default_metric_unit"    : "KILOWATT",
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : true,
    "negative_allowed"       : false,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 0,
    "localizable"            : false,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_metric_negative'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_metric_negative"
        }
    },
    "code"                   : "a_metric_negative",
    "type"                   : "pim_catalog_metric",
    "group"                  : "attributeGroupB",
    "group_labels"           : {"en_US": "Attribute group B","fr_FR": "Groupe d'attribut B"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : "Temperature",
    "default_metric_unit"    : "CELSIUS",
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : true,
    "negative_allowed"       : true,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 0,
    "localizable"            : false,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_metric_without_decimal'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_metric_without_decimal"
        }
    },
    "code"                   : "a_metric_without_decimal",
    "type"                   : "pim_catalog_metric",
    "group"                  : "attributeGroupB",
    "group_labels"           : {"en_US": "Attribute group B","fr_FR": "Groupe d'attribut B"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : "Length",
    "default_metric_unit"    : "METER",
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : false,
    "negative_allowed"       : false,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 0,
    "localizable"            : false,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_metric_without_decimal_negative'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_metric_without_decimal_negative"
        }
    },
    "code"                   : "a_metric_without_decimal_negative",
    "type"                   : "pim_catalog_metric",
    "group"                  : "attributeGroupC",
    "group_labels"           : {"en_US": "Attribute group C","fr_FR": "Groupe d'attribut C"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : "Temperature",
    "default_metric_unit"    : "CELSIUS",
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : false,
    "negative_allowed"       : true,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 0,
    "localizable"            : false,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : null,
    "default_value"          : null
}
JSON;

        $standardizedAttributes['a_multi_select'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes/a_multi_select"
        }
    },
    "code"                   : "a_multi_select",
    "type"                   : "pim_catalog_multiselect",
    "group"                  : "attributeGroupC",
    "group_labels"           : {"en_US": "Attribute group C","fr_FR": "Groupe d'attribut C"},
    "unique"                 : false,
    "useable_as_grid_filter" : false,
    "allowed_extensions"     : [],
    "metric_family"          : null,
    "default_metric_unit"    : null,
    "reference_data_name"    : null,
    "available_locales"      : [],
    "max_characters"         : null,
    "validation_rule"        : null,
    "validation_regexp"      : null,
    "wysiwyg_enabled"        : null,
    "number_min"             : null,
    "number_max"             : null,
    "decimals_allowed"       : null,
    "negative_allowed"       : null,
    "date_min"               : null,
    "date_max"               : null,
    "max_file_size"          : null,
    "minimum_input_length"   : null,
    "sort_order"             : 0,
    "localizable"            : false,
    "scopable"               : false,
    "labels"                 : {},
    "guidelines"             : {},
    "auto_option_sorting"    : false,
    "default_value"          : null
}
JSON;

        return $standardizedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

