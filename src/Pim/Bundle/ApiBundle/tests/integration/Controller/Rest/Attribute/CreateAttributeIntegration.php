<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Attribute;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class CreateAttributeIntegration extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenACategoryIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"an_attr_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/attributes/an_attr_text', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testFormatStandardWhenAnAttributeIsCreatedButIncompleted()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"an_incomplete_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('an_incomplete_text');

        $attributeStandard = [
            'code'                   => 'an_incomplete_text',
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => false,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => 0,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testCompleteAttributeCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"a_new_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "unique":false,
        "useable_as_grid_filter":false,
        "allowed_extensions":[],
        "metric_family":null,
        "default_metric_unit":null,
        "reference_data_name":null,
        "available_locales":[],
        "max_characters":null,
        "validation_rule":null,
        "validation_regexp":null,
        "wysiwyg_enabled":false,
        "number_min":null,
        "number_max":null,
        "decimals_allowed":false,
        "negative_allowed":false,
        "date_min":null,
        "date_max":null,
        "max_file_size":null,
        "minimum_input_length":0,
        "sort_order":12,
        "localizable":false,
        "scopable":false,
        "labels":[]
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_new_text');

        $attributeStandard = [
            'code'                   => 'a_new_text',
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => false,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => 0,
            'sort_order'             => 12,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';

        $expectedContent = [
            'code'    => 400,
            'message' => 'Invalid json message received',
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAttributeCodeAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "a_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'code',
                    'message' => 'This value is already used.',
                ]
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAttributeCodeIsNotScalar()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":[]
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "code" expects a scalar as data, "array" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ]
            ]
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAttributeGroupIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"an_incomplete_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupC"
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "group" expects a valid code. The attribute group does not exist, "attributeGroupC" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ]
            ]
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAttributeTypeIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "type":null
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "type" does not expect an empty value. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ]
            ]
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAttributeTypeIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"an_incomplete_text",
        "type":"pim_catalog_matrix",
        "group":"attributeGroupC"
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "type" expects a valid attribute type. The attribute type does not exist, "pim_catalog_matrix" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ]
            ]
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "a_",
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "extra_property": ""
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenADateIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"an_invalid_date",
        "type":"pim_catalog_date",
        "date_min":"a date"
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "date_min" expects a string with the format "yyyy-mm-dd" as data, "a date" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ]
            ]
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAvailableLocalesIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "available_locales":["akeneo_PIM"]
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "available_locales" expects a valid locale code. The locale does not exist, "akeneo_PIM" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ]
            ]
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenArrayExpectedValueHasAnInvalidStructure()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "available_locales":{
            "en_US": []
        }
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "available_locales" expects an array with valid data, one of the "available_locales" values is not a scalar. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ]
            ]
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAllowedExtensionsIsNullCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "allowed_extensions":null
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "allowed_extensions" expects an array as data, "NULL" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ],
            ],
        ];

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLabelsIsNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels":null
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array as data, "NULL" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ],
            ],
        ];

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAvailableLocalesIsNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "available_locales":null
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "available_locales" expects an array as data, "NULL" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf('https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute', $version),
                ],
            ],
        ];

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLocaleCodeInLabelsIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"unknown_locale",
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "unique":false,
        "useable_as_grid_filter":false,
        "allowed_extensions":[],
        "metric_family":null,
        "default_metric_unit":null,
        "reference_data_name":null,
        "available_locales":[],
        "max_characters":null,
        "validation_rule":null,
        "validation_regexp":null,
        "wysiwyg_enabled":false,
        "number_min":null,
        "number_max":null,
        "decimals_allowed":false,
        "negative_allowed":false,
        "date_min":null,
        "date_max":null,
        "max_file_size":null,
        "minimum_input_length":0,
        "sort_order":12,
        "localizable":false,
        "scopable":false,
        "labels": {
            "":"label"
        }
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'labels',
                    'message' => 'The locale "" does not exist.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLocaleCodeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"unknown_locale",
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "unique":false,
        "useable_as_grid_filter":false,
        "allowed_extensions":[],
        "metric_family":null,
        "default_metric_unit":null,
        "reference_data_name":null,
        "available_locales":[],
        "max_characters":null,
        "validation_rule":null,
        "validation_regexp":null,
        "wysiwyg_enabled":false,
        "number_min":null,
        "number_max":null,
        "decimals_allowed":false,
        "negative_allowed":false,
        "date_min":null,
        "date_max":null,
        "max_file_size":null,
        "minimum_input_length":0,
        "sort_order":12,
        "localizable":false,
        "scopable":false,
        "labels": {
            "foo": "label"
        }
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'labels',
                    'message' => 'The locale "foo" does not exist.',
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
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
