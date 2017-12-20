<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListAssetIntegration extends AbstractAssetTestCase
{
    public function testCreateAndUpdateAListOfAssets()
    {
        $data = <<<JSON
            {"code": "cat", "description": "foo"}
            {"code": "new_asset"}
            {"code": "new_asset"}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"cat","status_code":204}
{"line":2,"code":"new_asset","status_code":201}
{"line":3,"code":"new_asset","status_code":204}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/assets', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedAssets = [
            'cat' => [
                'code' => 'cat',
                'localizable' => true,
                'description' => 'foo',
                'end_of_use' => '2041-04-02T00:00:00+01:00',
                'tags' => ['animal'],
                'categories' => ['asset_main_catalog']
            ],
            'new_asset' => [
                'code' => 'new_asset',
                'localizable' => false,
                'description' => null,
                'end_of_use' => null,
                'tags' => [],
                'categories' => []
            ]
        ];

        $this->assertSameAssets($expectedAssets['cat'], 'cat');
        $this->assertSameAssets($expectedAssets['new_asset'], 'new_asset');
    }

    /**
     * Should be an integration test.
     */
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/assets', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * Should be an integration test.
     */
    public function testPartialUpdateListWithTooManyResources()
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $maxNumberResources = $this->getMaxNumberResources();

        for ($i = 0; $i < $maxNumberResources + 1; $i++) {
            $data[] = sprintf('{"code": "my_code_%s"}', $i);
        }
        $data = implode(PHP_EOL, $data);

        $expectedContent = <<<JSON
            {
                "code": 413,
                "message": "Too many resources to process, ${maxNumberResources} is the maximum allowed."
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/assets', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $response->getStatusCode());
    }

    /**
     * Should be an integration test.
     */
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

        $data = <<<JSON
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

        $expectedContent = <<<JSON
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/assets', [], [], [], $data);
        $httpResponse = $response['http_response'];


        $this->assertSame($expectedContent, $response['content']);
        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
    }

    /**
     * Should be an integration test.
     */
    public function testErrorWhenCodeIsMissing()
    {
        $data = <<<JSON
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/assets', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * Should be an integration test.
     */
    public function testUpdateAssetWhenUpdaterFailed()
    {
        $data = <<<JSON
            {"code": "foo", "unknown_property":"foo"}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"foo","status_code":422,"message":"Property \"unknown_property\" does not exist. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#post_asset"}}}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/assets', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateAssetWhenValidationFailed()
    {
        $data = <<<JSON
            {"code": "foo,"}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"foo,","status_code":422,"message":"Validation failed.","errors":[{"property":"code","message":"Asset code may contain only letters, numbers and underscores"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/assets', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * Should be an integration test.
     */
    public function testPartialUpdateListWithBadContentType()
    {
        $data = <<<JSON
            {"code": "my_code"}
JSON;

        $expectedContent = <<<JSON
            {
                "code": 415,
                "message": "\"application\/json\" in \"Content-Type\" header is not valid. Only \"application\/vnd.akeneo.collection+json\" is allowed."
            }
JSON;

        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', 'application/json');
        $client->request('PATCH', 'api/rest/v1/assets', [], [], [], $data);

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
     * @param array  $expectedAsset normalized data of the asset that should be created
     * @param string $code             code of the asset that should be created
     */
    protected function assertSameAssets(array $expectedAsset, $code)
    {
        $asset = $this->get('pimee_product_asset.repository.asset')->findOneByIdentifier($code);
        $normalizer = $this->get('pimee_product_asset.normalizer.standard.asset');
        $standardizedAsset = $normalizer->normalize($asset);

        $expectedAsset = $this->sanitizeNormalizedAsset($expectedAsset);
        $standardizedAsset = $this->sanitizeNormalizedAsset($standardizedAsset);

        $this->assertSame($expectedAsset, $standardizedAsset);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
