<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListVariantProductIntegration extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createProduct('product_family', [
            'family' => 'familyA2',
        ]);
    }

    public function testCreateAndUpdateAListOfProducts()
    {
        $data =
<<<JSON
    {"identifier": "product_family", "family": "familyA1"}
    {"identifier": "my_identifier", "family": "familyA2"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"identifier":"product_family","status_code":204}
{"line":2,"identifier":"my_identifier","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedProducts = [
            'product_family' => [
                'identifier'    => 'product_family',
                'family'        => 'familyA1',
                'parent'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => [],
                'enabled'       => true,
                'values'        => [
                    'sku' => [
                        ['locale' => null, 'scope' => null, 'data' => 'product_family'],
                    ],
                ],
                'created'       => '2016-06-14T13:12:50+02:00',
                'updated'       => '2016-06-14T13:12:50+02:00',
                'associations'  => [],
            ],
            'my_identifier'  => [
                'identifier'    => 'my_identifier',
                'family'        => 'familyA2',
                'parent'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => [],
                'enabled'       => true,
                'values'        => [
                    'sku' => [
                        ['locale' => null, 'scope' => null, 'data' => 'my_identifier'],
                    ],
                ],
                'created'       => '2016-06-14T13:12:50+02:00',
                'updated'       => '2016-06-14T13:12:50+02:00',
                'associations'  => [],
            ],
        ];

        $this->assertSameProducts($expectedProducts['product_family'], 'product_family');
        $this->assertSameProducts($expectedProducts['my_identifier'], 'my_identifier');
    }

    public function testCreateAndUpdateSameProduct()
    {
        $data =
<<<JSON
    {"identifier": "my_identifier"}
    {"identifier": "my_identifier"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"identifier":"my_identifier","status_code":201}
{"line":2,"identifier":"my_identifier","status_code":204}
JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithMaxNumberOfResourcesAllowed()
    {
        $maxNumberResources = $this->getMaxNumberResources();

        for ($i = 0; $i < $maxNumberResources; $i++) {
            $data[] = sprintf('{"identifier": "my_identifier_%s"}', $i);
        }
        $data = implode(PHP_EOL, $data);

        for ($i = 0; $i < $maxNumberResources; $i++) {
            $expectedContent[] = sprintf('{"line":%s,"identifier":"my_identifier_%s","status_code":201}', $i + 1, $i);
        }
        $expectedContent = implode(PHP_EOL, $expectedContent);

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
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
            $data[] = sprintf('{"identifier": "my_identifier_%s"}', $i);
        }
        $data = implode(PHP_EOL, $data);

        $expectedContent =
<<<JSON
    {
        "code": 413,
        "message": "Too many resources to process, ${maxNumberResources} is the maximum allowed."
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products', [], [], [], $data);

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
            'line_too_long_1' => '{"identifier":"foo"}' . str_repeat('a', $this->getBufferSize()),
            'line_too_long_2' => '{"identifier":"foo"}' . str_repeat(' ', $this->getBufferSize()),
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

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];


        $this->assertSame($expectedContent, $response['content']);
        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
    }

    public function testErrorWhenIdentifierIsMissing()
    {
        $data =
<<<JSON
    {"code": "my_identifier"}
    {"identifier": null}
    {"identifier": ""}
    {"identifier": " "}
    {}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"status_code":422,"message":"Identifier is missing."}
{"line":2,"status_code":422,"message":"Identifier is missing."}
{"line":3,"status_code":422,"message":"Identifier is missing."}
{"line":4,"status_code":422,"message":"Identifier is missing."}
{"line":5,"status_code":422,"message":"Identifier is missing."}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateWhenUpdaterFailed()
    {
        $data =
<<<JSON
    {"identifier": "foo", "variant_group":"bar"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"identifier":"foo","status_code":422,"message":"Property \"variant_group\" expects a valid variant group code. The variant group does not exist, \"bar\" given. Check the standard format documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateWhenValidationFailed()
    {
        $data =
<<<JSON
    {"identifier": "foo,"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"identifier":"foo,","status_code":422,"message":"Validation failed.","errors":[{"property":"identifier","message":"This field should not contain any comma or semicolon."}]}
JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testPartialUpdateListWithBadContentType()
    {
        $data =
<<<JSON
    {"identifier": "my_identifier"}
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
        $client->request('PATCH', 'api/rest/v1/products', [], [], [], $data);

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
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier      identifier of the product that should be created
     */
    protected function assertSameProducts(array $expectedProduct, $identifier)
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $standardizedProduct = $this->get('pim_serializer')->normalize($product, 'standard');

        NormalizedProductCleaner::clean($expectedProduct);
        NormalizedProductCleaner::clean($standardizedProduct);

        $this->assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }
}
