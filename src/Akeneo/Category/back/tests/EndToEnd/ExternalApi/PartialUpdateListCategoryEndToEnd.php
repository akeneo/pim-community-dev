<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListCategoryEndToEnd extends ApiTestCase
{
    /**
     * @group critical
     */
    public function testCreateAndUpdateAListOfCategories(): void
    {
        $data =
<<<JSON
    {"code": "categoryA2","labels":{"en_US":"category A2"}}
    {"code": "categoryD","parent":"master"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"categoryA2","status_code":204}
{"line":2,"code":"categoryD","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedCategories = [
            'categoryA2' => [
                'code' => 'categoryA2',
                'parent' => 'categoryA',
                'updated' => '2016-06-14T13:12:50+02:00',
                'labels' => [
                    'en_US' => 'category A2',
                ],
            ],
            'categoryD' => [
                'code' => 'categoryD',
                'parent' => 'master',
                'updated' => '2016-06-14T13:12:50+02:00',
                'labels' => [],
            ],
        ];

        $this->assertSameCategories($expectedCategories['categoryA2'], 'categoryA2');
        $this->assertSameCategories($expectedCategories['categoryD'], 'categoryD');
    }

    public function testCreateAndUpdateSameCategory(): void
    {
        $data =
<<<JSON
    {"code": "categoryD"}
    {"code": "categoryD"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"categoryD","status_code":201}
{"line":2,"code":"categoryD","status_code":204}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithMaxNumberOfResourcesAllowed(): void
    {
        $maxNumberResources = $this->getMaxNumberResources();

        $dataRows = [];
        $expectedContentRows = [];

        for ($i = 0; $i < $maxNumberResources; ++$i) {
            $dataRows[] = sprintf('{"code": "my_code_%s"}', $i);
            $expectedContentRows[] = sprintf('{"line":%s,"code":"my_code_%s","status_code":201}', $i + 1, $i);
        }

        $data = implode(PHP_EOL, $dataRows);
        $expectedContent = implode(PHP_EOL, $expectedContentRows);

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithTooManyResources(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $maxNumberResources = $this->getMaxNumberResources();

        $dataRows = [];
        for ($i = 0; $i < $maxNumberResources + 1; ++$i) {
            $dataRows[] = sprintf('{"identifier": "my_code_%s"}', $i);
        }
        $data = implode(PHP_EOL, $dataRows);

        $expectedContent =
<<<JSON
    {
        "code": 413,
        "message": "Too many resources to process, $maxNumberResources is the maximum allowed."
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertResponseStatusCodeSame(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    public function testPartialUpdateListWithInvalidAndTooLongLines(): void
    {
        $line = [
            'invalid_json_1' => str_repeat('a', $this->getBufferSize() - 1),
            'invalid_json_2' => str_repeat('a', $this->getBufferSize()),
            'invalid_json_3' => '',
            'line_too_long_1' => '{"code":"foo"}'.str_repeat('a', $this->getBufferSize()),
            'line_too_long_2' => '{"code":"foo"}'.str_repeat(' ', $this->getBufferSize()),
            'line_too_long_3' => str_repeat('a', $this->getBufferSize() + 1),
            'line_too_long_4' => str_repeat('a', $this->getBufferSize() + 2),
            'line_too_long_5' => str_repeat('a', $this->getBufferSize() * 2),
            'line_too_long_6' => str_repeat('a', $this->getBufferSize() * 5),
            'invalid_json_4' => str_repeat('a', $this->getBufferSize()),
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame($expectedContent, $response['content']);
        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
    }

    public function testErrorWhenIdentifierIsMissing(): void
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateCategoryWhenUpdaterFailed(): void
    {
        $data =
<<<JSON
    {"code": "foo", "parent":"bar"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"foo","status_code":422,"message":"Property \"parent\" expects a valid category code. The category does not exist, \"bar\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_categories__code_"}}}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateCategoryWhenValidationFailed(): void
    {
        $data =
<<<JSON
    {"code": "foo,"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"foo,","status_code":422,"message":"Validation failed.","errors":[{"property":"code","message":"Category code may contain only letters, numbers and underscores"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/categories', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithBadContentType(): void
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
        $client->request('PATCH', 'api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    protected function getBufferSize(): mixed
    {
        return $this->getParameter('api_input_buffer_size');
    }

    protected function getMaxNumberResources(): mixed
    {
        return $this->getParameter('api_input_max_resources_number');
    }

    /**
     * @param array<string, mixed> $expectedCategory normalized data of the category that should be created
     * @param string $code code of the category that should be created
     */
    protected function assertSameCategories(array $expectedCategory, $code): void
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier($code);
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');
        $standardizedCategory = $normalizer->normalize($category);

        NormalizedCategoryCleaner::clean($expectedCategory);
        NormalizedCategoryCleaner::clean($standardizedCategory);

        $this->assertSame($expectedCategory, $standardizedCategory);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
