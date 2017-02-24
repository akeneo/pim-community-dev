<?php

namespace tests\integration\Pim\Bundle\ApiBundle\Controller\Rest\Attribute;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateAttributeIntergration extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAttributeIsUpdated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "a_text"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_text', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('http://localhost/api/rest/v1/attributes/a_text', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testHttpHeadersInResponseWhenAnAttributeIsCreated()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "a_new_text",
        "type": "pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_new_text', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/attributes/a_new_text', $response->headers->get('location'));
        $this->assertSame(null, json_decode($response->getContent(), true));
    }

    public function testStandardFormatWhenAnAttributeIsCreatedButIncomplete()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "an_incomplete_text",
        "type": "pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);

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

    public function testResponseWhenAnAttributeIsCreatedWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'attributeType',
                    'message' => 'This value should not be blank.',
                ],
                [
                    'field'   => 'group',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/attributes/new_incomplete_text', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testCompleteAttributeCreationWithCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "a_new_text_with_code",
        "type": "pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/attributes/a_new_text_with_code', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_new_text_with_code');
        $attributeStandard = [
            'code'                   => 'a_new_text_with_code',
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

    public function testCompleteAttributeCreationWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "type": "pim_catalog_text",
        "group":"attributeGroupA"
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/attributes/a_new_text_without_code', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_new_text_without_code');
        $attributeStandard = [
            'code'                   => 'a_new_text_without_code',
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

    public function testAttributePartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);

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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testAttributePartialUpdateWithCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code":"an_incomplete_text",
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "max_characters": 100,
        "metric_family": null,
        "default_metric_unit": null
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);

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
            'max_characters'         => 100,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testAttributePartialUpdateWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "type":"pim_catalog_text",
        "group":"attributeGroupA",
        "max_characters": 150
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);

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
            'max_characters'         => 150,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
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

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);
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

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenTryingToUpdateTheCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "new_code"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'field'   => 'code',
                    'message' => 'This property cannot be changed.',
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);

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
        "extra_property": ""
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf(
                        'https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute',
                        $version
                    ),
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenLabelsIsNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "labels": null
    }
JSON;

        $version = substr(Version::VERSION, 0, 3);
        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => sprintf(
                        'https://docs.akeneo.com/%s/reference/standard_format/other_entities.html#attribute',
                        $version
                    ),
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/attributes/an_incomplete_text', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    public function testResponseWhenAnAttributeIsCreatedWithInconsistentCodes()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "inconsistent_code2"
    }
JSON;

        $expectedContent = [
            'code'    => 422,
            'message' => 'The code "inconsistent_code2" provided in the request body must match the code "inconsistent_code1" provided in the url.',
        ];

        $client->request('PATCH', 'api/rest/v1/attributes/inconsistent_code1', [], [], [], $data);

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
