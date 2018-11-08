<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class CreateAttributeEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAttributeIsCreated()
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

    public function testStandardFormatWhenAnAttributeIsCreatedButIncomplete()
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
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => [],
            'auto_option_sorting'    => null,
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
        "wysiwyg_enabled":null,
        "number_min":null,
        "number_max":null,
        "decimals_allowed":null,
        "negative_allowed":null,
        "date_min":null,
        "date_max":null,
        "max_file_size":null,
        "minimum_input_length":null,
        "sort_order":12,
        "localizable":false,
        "scopable":false,
        "labels":[],
        "auto_option_sorting":null
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
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 12,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => [],
            'auto_option_sorting'    => null,
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute');

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testAttributeCreationWithEmptyLabels()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"empty_label_attribute",
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "labels": {
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('empty_label_attribute');

        $attributeStandard = [
            'code'                   => 'empty_label_attribute',
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
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => [],
            'auto_option_sorting'    => null,
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
                    'property' => 'code',
                    'message'  => 'This value is already used.',
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

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "code" expects a scalar as data, "array" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        "group":"attributeGroupD"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "group" expects a valid code. The attribute group does not exist, "attributeGroupD" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "type" does not expect an empty value. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "type" expects a valid attribute type. The attribute type does not exist, "pim_catalog_matrix" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "date_min" expects a string with the format "yyyy-mm-dd" as data, "a date" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "available_locales" expects a valid locale code. The locale does not exist, "akeneo_PIM" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "available_locales" expects an array with valid data, one of the "available_locales" values is not a scalar. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "allowed_extensions" expects an array as data, "NULL" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array as data, "NULL" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "available_locales" expects an array as data, "NULL" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_attributes',
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
                    'property' => 'labels',
                    'message'  => 'The locale "" does not exist.',
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
                    'property' => 'labels',
                    'message'  => 'The locale "foo" does not exist.',
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
        return $this->catalog->useTechnicalCatalog();
    }
}
