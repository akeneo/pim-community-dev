<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeOption\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListAttributeOptionEndToEnd extends ApiTestCase
{
    public function testHttpHeadersInResponseWhenAnAttributeOptionIsUpdated()
    {
        $data = <<<JSON
    {"code": "optionA","labels": {"en_US": "A Option"}}
    {"code": "optionB","labels": {"en_US": "B Option"}}
    {"code": "optionC","labels": {"en_US": "Option C"}}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"optionA","status_code":204}
{"line":2,"code":"optionB","status_code":204}
{"line":3,"code":"optionC","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedOptions = [
            'optionA' => [
                'code' => 'optionA',
                'attribute' => 'a_multi_select',
                'sort_order' => 10,
                'labels' => [
                    'en_US' => 'A Option',
                ],
            ],
            'optionB' => [
                'code' => 'optionB',
                'attribute' => 'a_multi_select',
                'sort_order' => 20,
                'labels' => [
                    'en_US' => 'B Option',
                ],
            ],
            'optionC' => [
                'code' => 'optionC',
                'attribute' => 'a_multi_select',
                'sort_order' => 21,
                'labels' => [
                    'en_US' => 'Option C',
                ],
            ],
        ];

        $this->assertSameAttributeOptions($expectedOptions['optionA'], 'a_multi_select.optionA');
        $this->assertSameAttributeOptions($expectedOptions['optionB'], 'a_multi_select.optionB');
        $this->assertSameAttributeOptions($expectedOptions['optionC'], 'a_multi_select.optionC');
    }

    public function testPartialUpdateListWithMaxNumberOfResourcesAllowed()
    {
        $maxNumberResources = $this->getMaxNumberResources();

        $json = '{"code": "my_code_%s", "labels": {"en_US": "Option"}}';
        for ($i = 0; $i < $maxNumberResources; $i++) {
            $data[] = sprintf($json, $i);
        }
        $data = implode(PHP_EOL, $data);

        for ($i = 0; $i < $maxNumberResources; $i++) {
            $expectedContent[] = sprintf('{"line":%s,"code":"my_code_%s","status_code":201}', $i + 1, $i);
        }
        $expectedContent = implode(PHP_EOL, $expectedContent);

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
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
            $data[] = sprintf('{"code": "my_code_%s"}', $i);
        }
        $data = implode(PHP_EOL, $data);

        $expectedContent =
            <<<JSON
    {
        "code": 413,
        "message": "Too many resources to process, ${maxNumberResources} is the maximum allowed."
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateAttributeOptionWhenUpdaterFailed()
    {
        $data =
            <<<JSON
    {"code": "foo", "attributes":"bar"}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"code":"foo","status_code":422,"message":"Property \"attributes\" does not exist. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_attributes__attribute_code__options__code_"}}}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateAttributeOptionWhenValidationFailed()
    {
        $data =
            <<<JSON
    {"code": "foo,"}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"code":"foo,","status_code":422,"message":"Validation failed.","errors":[{"property":"code","message":"Option code may contain only letters, numbers and underscores"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);
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
        $client->request('PATCH', 'api/rest/v1/attributes/a_multi_select/options', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    protected function getMaxNumberResources()
    {
        return $this->getParameter('api_input_max_resources_number');
    }

    protected function getBufferSize()
    {
        return $this->getParameter('api_input_buffer_size');
    }

    /**
     * @param array  $expectedAttributeOption normalized data of the attribute option that should be created
     * @param string $code           code of the attribute option that should be created
     */
    protected function assertSameAttributeOptions(array $expectedAttributeOption, $code): void
    {
        $attributeOption = $this->get('pim_catalog.repository.attribute_option')->findOneByIdentifier($code);
        $normalizer = $this->get('pim_catalog.normalizer.standard.attribute_option');
        $standardizedAttributeOption = $normalizer->normalize($attributeOption);

        $this->assertSame($expectedAttributeOption, $standardizedAttributeOption);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
