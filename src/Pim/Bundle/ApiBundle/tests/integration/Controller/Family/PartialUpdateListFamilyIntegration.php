<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Family;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListFamilyIntegration extends ApiTestCase
{
    public function testCreateAndUpdateAListOfFamilies()
    {
        $data =
<<<JSON
    {"code": "familyA1","labels":{"en_US":"family A1"}}
    {"code": "familyC","attributes":["a_yes_no"],"attribute_as_label":"sku"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"familyA1","status_code":204}
{"line":2,"code":"familyC","status_code":201}
JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/families', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedFamilies = [
            'familyA1' => [
                'code'                   => 'familyA1',
                'attributes'             => ['a_date', 'a_file', 'a_localizable_image', 'sku'],
                'attribute_as_label'     => 'sku',
                'attribute_requirements' => [
                    'ecommerce'       => ['a_date', 'a_file', 'sku'],
                    'ecommerce_china' => ['sku'],
                    'tablet'          => ['a_file', 'a_localizable_image', 'sku']
                ],
                'labels'                 => [
                    'en_US' => 'family A1'
                ]
            ],
            'familyC' => [
                'code'                   => 'familyC',
                'attributes'             => ['a_yes_no', 'sku'],
                'attribute_as_label'     => 'sku',
                'attribute_requirements' => [
                    'ecommerce'       => ['sku'],
                    'ecommerce_china' => ['sku'],
                    'tablet'          => ['sku']
                ],
                'labels'                 => []
            ]
        ];

        $this->assertSameFamilies($expectedFamilies['familyA1'], 'familyA1');
        $this->assertSameFamilies($expectedFamilies['familyC'], 'familyC');
    }

    public function testCreateAndUpdateSameFamily()
    {
        $data =
<<<JSON
    {"code": "familyC"}
    {"code": "familyC"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"familyC","status_code":201}
{"line":2,"code":"familyC","status_code":204}
JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/families', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithMaxNumberOfResourcesAllowed()
    {
        $maxNumberResources = $this->getMaxNumberResources();

        for ($i = 0; $i < $maxNumberResources; $i++) {
            $data[] = sprintf('{"code": "my_code_%s"}', $i);
        }
        $data = implode(PHP_EOL, $data);

        for ($i = 0; $i < $maxNumberResources; $i++) {
            $expectedContent[] = sprintf('{"line":%s,"code":"my_code_%s","status_code":201}', $i + 1, $i);
        }
        $expectedContent = implode(PHP_EOL, $expectedContent);

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/families', [], [], [], $data);
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

        $client->request('PATCH', 'api/rest/v1/families', [], [], [], $data);

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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/families', [], [], [], $data);
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/families', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateFamilyWhenUpdaterFailed()
    {
        $data =
<<<JSON
    {"code": "foo", "attributes":"bar"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"foo","status_code":422,"message":"Property \"attributes\" expects an array as data, \"string\" given. Check the standard format documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_families__code_"}}}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/families', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateFamilyWhenValidationFailed()
    {
        $data =
<<<JSON
    {"code": "foo,"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"foo,","status_code":422,"message":"Validation failed.","errors":[{"property":"code","message":"Family code may contain only letters, numbers and underscores"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/families', [], [], [], $data);
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
        $client->request('PATCH', 'api/rest/v1/families', [], [], [], $data);

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
     * Execute a request where the response is streamed by chunk.
     *
     * The whole content of the request and the whole content of the response
     * are loaded in memory.
     * Therefore, do not use this function on with an high input/output volumetry.
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     * @param string $content
     * @param bool   $changeHistory
     *
     * @return array
     */
    protected function executeStreamRequest(
        $method,
        $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $content = null,
        $changeHistory = true
    ) {
        $streamedContent = '';

        ob_start(function($buffer) use (&$streamedContent) {
            $streamedContent .= $buffer;

            return '';
        });

        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);
        $client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        ob_end_flush();

        $response = [
            'http_response' => $client->getResponse(),
            'content'       => $streamedContent,
        ];

        return $response;
    }

    /**
     * @param array  $expectedFamily normalized data of the family that should be created
     * @param string $code           code of the family that should be created
     */
    protected function assertSameFamilies(array $expectedFamily, $code)
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($code);
        $normalizer = $this->get('pim_catalog.normalizer.standard.family');
        $standardizedFamily = $normalizer->normalize($family);

        $this->assertSame($expectedFamily, $standardizedFamily);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }
}
