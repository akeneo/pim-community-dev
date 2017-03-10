<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Attribute;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListAttributeIntegration extends ApiTestCase
{
    public function testListAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes?page=1&limit=10"
        },
        "next": {
            "href": "http://localhost/api/rest/v1/attributes?page=2&limit=10"
        },
        "last": {
            "href": "http://localhost/api/rest/v1/attributes?page=3&limit=10"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/attributes?page=1&limit=10"
        }
    },
    "current_page": 1,
    "pages_count": 3,
    "items_count": 27,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/sku"
                    }
                },
                "code"                   : "sku",
                "type"                   : "pim_catalog_identifier",
                "group"                  : "attributeGroupA",
                "unique"                 : true,
                "useable_as_grid_filter" : true,
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
                "labels"                 : []
            },
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
                "minimum_input_length"   : 0,
                "sort_order"             : 2,
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            },
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
                "allowed_extensions"     : ["pdf", "doc", "docx"],
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
                "labels"                 : []
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/an_image"
                    }
                },
                "code"                   : "an_image",
                "type"                   : "pim_catalog_image",
                "group"                  : "attributeGroupB",
                "unique"                 : false,
                "useable_as_grid_filter" : false,
                "allowed_extensions"     : ["jpg", "gif", "png"],
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
                "max_file_size"          : "500.00",
                "minimum_input_length"   : null,
                "sort_order"             : 0,
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            },
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
                "minimum_input_length"   : 0,
                "sort_order"             : null,
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            },
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
                "labels"                 : []
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/a_metric_without_decimal_negative"
                    }
                },
                "code"                   : "a_metric_without_decimal_negative",
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
                "decimals_allowed"       : false,
                "negative_allowed"       : true,
                "date_min"               : null,
                "date_max"               : null,
                "max_file_size"          : null,
                "minimum_input_length"   : null,
                "sort_order"             : 0,
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            },
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
                "labels"                 : []
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/a_multi_select"
                    }
                },
                "code"                   : "a_multi_select",
                "type"                   : "pim_catalog_multiselect",
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
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/a_number_float"
                    }
                },
                "code"                   : "a_number_float",
                "type"                   : "pim_catalog_number",
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
                "decimals_allowed"       : true,
                "negative_allowed"       : false,
                "date_min"               : null,
                "date_max"               : null,
                "max_file_size"          : null,
                "minimum_input_length"   : null,
                "sort_order"             : 0,
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testListAttributesWithLimitAndPage()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes?limit=5&page=2');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/attributes?limit=5&page=2"
        },
        "next": {
            "href": "http://localhost/api/rest/v1/attributes?limit=5&page=3"
        },
        "last": {
            "href": "http://localhost/api/rest/v1/attributes?limit=5&page=6"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/attributes?limit=5&page=1"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/attributes?limit=5&page=1"
        }
    },
    "current_page": 2,
    "pages_count": 6,
    "items_count": 27,
    "_embedded": {
        "items": [
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
                "labels"                 : []
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/a_metric_without_decimal_negative"
                    }
                },
                "code"                   : "a_metric_without_decimal_negative",
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
                "decimals_allowed"       : false,
                "negative_allowed"       : true,
                "date_min"               : null,
                "date_max"               : null,
                "max_file_size"          : null,
                "minimum_input_length"   : null,
                "sort_order"             : 0,
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            },
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
                "labels"                 : []
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/a_multi_select"
                    }
                },
                "code"                   : "a_multi_select",
                "type"                   : "pim_catalog_multiselect",
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
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/attributes/a_number_float"
                    }
                },
                "code"                   : "a_number_float",
                "type"                   : "pim_catalog_number",
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
                "decimals_allowed"       : true,
                "negative_allowed"       : false,
                "date_min"               : null,
                "date_max"               : null,
                "max_file_size"          : null,
                "minimum_input_length"   : null,
                "sort_order"             : 0,
                "localizable"            : false,
                "scopable"               : false,
                "labels"                 : []
            }
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
            "href": "http://localhost/api/rest/v1/attributes?limit=100&page=2"
        },
        "last": {
            "href": "http://localhost/api/rest/v1/attributes?limit=100&page=1"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/attributes?limit=100&page=1"
        }
    },
    "current_page": 2,
    "pages_count": 1,
    "items_count": 27,
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
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
