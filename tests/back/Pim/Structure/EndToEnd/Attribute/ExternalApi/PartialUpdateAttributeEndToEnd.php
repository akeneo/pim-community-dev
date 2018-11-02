<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class PartialUpdateAttributeEndToEnd extends ApiTestCase
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

    public function testResponseWhenAnAttributeIsCreatedWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'type',
                    'message'  => 'This value should not be blank.',
                ],
                [
                    'property' => 'group',
                    'message'  => 'This value should not be blank.',
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

    public function testAttributePartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/attributes/a_metric', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_metric');
        $attributeStandard = [
            'code'                   => 'a_metric',
            'type'                   => 'pim_catalog_metric',
            'group'                  => 'attributeGroupB',
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => 'Power',
            'default_metric_unit'    => 'KILOWATT',
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testAttributePartialUpdateWithCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
                {
        "code":"a_metric",
        "type":"pim_catalog_metric",
        "group":"attributeGroupA",
        "default_metric_unit":"WATT" 
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_metric', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_metric');
        $attributeStandard = [
            'code'                   => 'a_metric',
            'type'                   => 'pim_catalog_metric',
            'group'                  => 'attributeGroupA',
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => 'Power',
            'default_metric_unit'    => 'WATT',
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testAttributePartialUpdateWithoutCodeProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
                {
        "type":"pim_catalog_metric",
        "group":"attributeGroupA",
        "default_metric_unit":"WATT" 
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_metric', [], [], [], $data);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_metric');
        $attributeStandard = [
            'code'                   => 'a_metric',
            'type'                   => 'pim_catalog_metric',
            'group'                  => 'attributeGroupA',
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => 'Power',
            'default_metric_unit'    => 'WATT',
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => false,
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
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($attributeStandard, $normalizer->normalize($attribute));
    }

    public function testAttributePartialUpdateWithEmptyLabels()
    {
        $initLabels = [
            'labels' => [
                'en_US' => 'Family A2 US',
                'fr_FR' => 'Family A2 FR',
                'de_DE' => 'Family A2 DE',
            ],
        ];

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_text');
        $this->get('pim_catalog.updater.attribute')->update($attribute, $initLabels);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $data =
<<<JSON
    {
        "labels": {
            "en_US": null,
            "fr_FR": ""
        }
    }
JSON;

        $attributeStandard = [
            'code'                   => 'a_text',
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => 200,
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
            'sort_order'             => 6,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => [
                'de_DE' => 'Family A2 DE',
            ],
            'auto_option_sorting'    => null,
        ];

        $client = $this->createAuthenticatedClient();
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute');

        $client->request('PATCH', 'api/rest/v1/attributes/a_text', [], [], [], $data);

        $response = $client->getResponse();
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_text');

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
                    'property' => 'code',
                    'message'  => 'This property cannot be changed.',
                ],
            ],
        ];

        $client->request('PATCH', 'api/rest/v1/attributes/a_metric', [], [], [], $data);

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

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "extra_property" does not exist. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_attributes__code_'
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

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "labels" expects an array as data, "NULL" given. Check the expected format on the API documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#patch_attributes__code_'
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
        return $this->catalog->useTechnicalCatalog();
    }
}
