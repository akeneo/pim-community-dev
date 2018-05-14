<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\tests\EndToEnd\ExternalApi\AssetCategory;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListAssetCategoryIntegration extends ApiTestCase
{
    public function testCreateAndUpdateAListOfAssetCategories()
    {
        $data = <<<JSON
            {"code": "asset_main_catalog","labels":{"en_US":"New label"}}
            {"code": "new_asset_category","parent":"asset_main_catalog"}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"asset_main_catalog","status_code":204}
{"line":2,"code":"new_asset_category","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedAssetCategories = [
            'asset_main_catalog' => [
                'code'   => 'asset_main_catalog',
                'parent' => null,
                'labels' => [
                    'en_US' => 'New label'
                ]
            ],
            'new_asset_category' => [
                'code'   => 'new_asset_category',
                'parent' => 'asset_main_catalog',
                'labels' => []
            ]
        ];

        $this->assertSameAssetCategories($expectedAssetCategories['asset_main_catalog'], 'asset_main_catalog');
        $this->assertSameAssetCategories($expectedAssetCategories['new_asset_category'], 'new_asset_category');
    }

    /**
     * Should be an integration test.
     */
    public function testCreateAndUpdateSameAssetCategory()
    {
        $data = <<<JSON
            {"code": "new_asset_category"}
            {"code": "new_asset_category"}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"new_asset_category","status_code":201}
{"line":2,"code":"new_asset_category","status_code":204}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);
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

        $client->request('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);

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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * Should be an integration test.
     */
    public function testUpdateAssetCategoryWhenUpdaterFailed()
    {
        $data = <<<JSON
            {"code": "foo", "parent":"bar"}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"foo","status_code":422,"message":"Property \"parent\" expects a valid category code. The category does not exist, \"bar\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_asset_categories__code_"}}}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateCategoryWhenValidationFailed()
    {
        $data = <<<JSON
            {"code": "foo,"}
JSON;

        $expectedContent = <<<JSON
{"line":1,"code":"foo,","status_code":422,"message":"Validation failed.","errors":[{"property":"code","message":"Category code may contain only letters, numbers and underscores"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);
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
        $client->request('PATCH', 'api/rest/v1/asset-categories', [], [], [], $data);

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
     * @param array  $expectedCategory normalized data of the asset category that should be created
     * @param string $code             code of the category that should be created
     */
    protected function assertSameAssetCategories(array $expectedCategory, $code)
    {
        $category = $this->get('pimee_product_asset.repository.category')->findOneByIdentifier($code);
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $standardizedCategory = $normalizer->normalize($category);

        $this->assertSame($expectedCategory, $standardizedCategory);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
