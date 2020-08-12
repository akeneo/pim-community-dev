<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class PartialUpdateListAttributeEndToEnd extends ApiTestCase
{
    public function testCreateAndUpdateAListOfAttributes()
    {
        $data =
<<<JSON
    {"code": "an_image","max_file_size": 800}
    {"code": "picture","allowed_extensions":["jpg","gif"],"type":"pim_catalog_image","group":"other"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"an_image","status_code":204}
{"line":2,"code":"picture","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedAttributes = [
            'an_image' => [
                'code'                   => 'an_image',
                'type'                   => 'pim_catalog_image',
                'group'                  => 'attributeGroupA',
                'unique'                 => false,
                'useable_as_grid_filter' => false,
                'allowed_extensions'     => ['jpg', 'gif', 'png'],
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
                'max_file_size'          => '800',
                'minimum_input_length'   => null,
                'sort_order'             => 0,
                'localizable'            => false,
                'scopable'               => false,
                'labels'                 => [],
                'auto_option_sorting'    => null,
            ],
            'picture' => [
                'code'                   => 'picture',
                'type'                   => 'pim_catalog_image',
                'group'                  => 'other',
                'unique'                 => false,
                'useable_as_grid_filter' => false,
                'allowed_extensions'     => ['jpg', 'gif'],
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
            ]
        ];

        $this->assertSameAttributes($expectedAttributes['an_image'], 'an_image');
        $this->assertSameAttributes($expectedAttributes['picture'], 'picture');
    }

    public function testCreateAndUpdateSameAttribute()
    {
        $data =
<<<JSON
    {"code": "an_attribute","type":"pim_catalog_text","group":"other"}
    {"code": "an_attribute"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"an_attribute","status_code":201}
{"line":2,"code":"an_attribute","status_code":204}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithMaxNumberOfResourcesAllowed()
    {
        $maxNumberResources = $this->getMaxNumberResources();

        for ($i = 0; $i < $maxNumberResources; $i++) {
            $data[] = sprintf('{"code": "my_code_%s", "type":"pim_catalog_text","group":"other"}', $i);
        }
        $data = implode(PHP_EOL, $data);

        for ($i = 0; $i < $maxNumberResources; $i++) {
            $expectedContent[] = sprintf('{"line":%s,"code":"my_code_%s","status_code":201}', $i + 1, $i);
        }
        $expectedContent = implode(PHP_EOL, $expectedContent);

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithTooManyResources()
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $maxNumberResources = $this->getMaxNumberResources();

        for ($i = 0; $i < $maxNumberResources + 1; $i++) {
            $data[] = sprintf('{"identifier": "my_code_%s"}', $i);
        }
        $data = implode(PHP_EOL, $data);

        $expectedContent =
<<<JSON
    {
        "code": 413,
        "message": "Too many resources to process, ${maxNumberResources} is the maximum allowed."
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $response->getStatusCode());
    }

    public function testPartialUpdateListWithInvalidAndTooLongLines()
    {
        $line = [
            'invalid_json_1'  => str_repeat('a', $this->getBufferSize() - 1),
            'invalid_json_2'  => str_repeat('a', $this->getBufferSize()),
            'invalid_json_3'  => '',
            'line_too_long_1' => '{"code":"foo"}' . str_repeat('a', $this->getBufferSize()),
            'line_too_long_2' => '{"code":"foo"}' . str_repeat(' ', $this->getBufferSize()),
            'line_too_long_3' => str_repeat('a', $this->getBufferSize() + 1),
            'line_too_long_4' => str_repeat('a', $this->getBufferSize() + 2),
            'line_too_long_5' => str_repeat('a', $this->getBufferSize() * 2),
            'line_too_long_6' => str_repeat('a', $this->getBufferSize() * 5),
            'invalid_json_4'  => str_repeat('a', $this->getBufferSize()),
        ];

        $data =
<<<JSON
${line['invalid_json_1']}
${line['invalid_json_2']}
${line['invalid_json_3']}
${line['line_too_long_1']}
${line['line_too_long_2']}
${line['line_too_long_3']}
${line['line_too_long_4']}
${line['line_too_long_5']}
${line['line_too_long_6']}
${line['invalid_json_4']}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"status_code":400,"message":"Invalid json message received"}
{"line":2,"status_code":400,"message":"Invalid json message received"}
{"line":3,"status_code":400,"message":"Invalid json message received"}
{"line":4,"status_code":413,"message":"Line is too long."}
{"line":5,"status_code":413,"message":"Line is too long."}
{"line":6,"status_code":413,"message":"Line is too long."}
{"line":7,"status_code":413,"message":"Line is too long."}
{"line":8,"status_code":413,"message":"Line is too long."}
{"line":9,"status_code":413,"message":"Line is too long."}
{"line":10,"status_code":400,"message":"Invalid json message received"}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];


        $this->assertSame($expectedContent, $response['content']);
        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
    }

    public function testErrorWhenIdentifierIsMissing()
    {
        $data =
<<<JSON
    {"identifier": "my_identifier"}
    {"code": null}
    {"code": ""}
    {"code": " "}
    {}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"status_code":422,"message":"Code is missing."}
{"line":2,"status_code":422,"message":"Code is missing."}
{"line":3,"status_code":422,"message":"Code is missing."}
{"line":4,"status_code":422,"message":"Code is missing."}
{"line":5,"status_code":422,"message":"Code is missing."}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateAttributeWhenUpdaterFailed()
    {
        $data =
<<<JSON
    {"code": "foo", "type":"bar"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"foo","status_code":422,"message":"Property \"type\" expects a valid attribute type. The attribute type does not exist, \"bar\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_attributes__code_"}}}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateAttributeWhenValidationFailed()
    {
        $data =
<<<JSON
    {"code": "foo,", "type":"pim_catalog_text","group":"other"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"foo,","status_code":422,"message":"Validation failed.","errors":[{"property":"code","message":"Attribute code may contain only letters, numbers and underscore"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithBadContentType()
    {
        $data =
<<<JSON
    {"code": "my_code"}
JSON;

        $expectedContent =
<<<JSON
    {
        "code": 415,
        "message": "\"application\/json\" in \"Content-Type\" header is not valid. Only \"application\/vnd.akeneo.collection+json\" is allowed."
    }
JSON;

        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', 'application/json');
        $client->request('PATCH', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    protected function getBufferSize()
    {
        return $this->getParameter('api_input_buffer_size');
    }

    protected function getMaxNumberResources()
    {
        return $this->getParameter('api_input_max_resources_number');
    }

    /**
     * @param array  $expectedAttribute normalized data of the attribute that should be created
     * @param string $code             code of the attribute that should be created
     */
    protected function assertSameAttributes(array $expectedAttribute, $code)
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute');
        $standardizedAttribute = $normalizer->normalize($attribute);

        $this->assertSame($expectedAttribute, $standardizedAttribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
