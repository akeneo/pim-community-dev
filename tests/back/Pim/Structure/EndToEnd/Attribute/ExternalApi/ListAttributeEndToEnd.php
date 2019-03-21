<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
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
	"items_count": 29,
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : null
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
    "auto_option_sorting"    : false
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
