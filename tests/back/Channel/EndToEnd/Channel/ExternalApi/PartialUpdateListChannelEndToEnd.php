<?php

namespace AkeneoTest\Channel\EndToEnd\Channel\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListChannelEndToEnd extends ApiTestCase
{
    public function testCreateAndUpdateAListOfChannels()
    {
        $data =
<<<JSON
{"code": "ecommerce","currencies": ["EUR"]}
{"code": "ecommerce_ch","category_tree": "master", "currencies": ["EUR"], "locales": ["fr_FR"]}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"ecommerce","status_code":204}
{"line":2,"code":"ecommerce_ch","status_code":201}
JSON;
        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/channels', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedChannels = [
            'ecommerce' => [
                'code'             => 'ecommerce',
                'currencies'       => ['EUR'],
                'locales'          => ['en_US'],
                'category_tree'    => 'master',
                'conversion_units' => [
                    'a_metric_without_decimal' => 'METER',
                    'a_metric'                 => 'KILOWATT',
                ],
                'labels'           => [
                    'en_US' => 'Ecommerce',
                    'fr_FR' => 'Ecommerce',
                ],
            ],
            'ecommerce_ch'       => [
                'code'       => 'ecommerce_ch',
                'currencies'       => ['EUR'],
                'locales'          => ['fr_FR'],
                'category_tree'    => 'master',
                'conversion_units' => [],
                'labels'           => [],
            ],
        ];

        $ecommerce = $this->getNormalizedChannel('ecommerce');
        $ecommerceCh = $this->getNormalizedChannel('ecommerce_ch');

        $this->assertSame($expectedChannels['ecommerce'], $ecommerce);
        $this->assertSame($expectedChannels['ecommerce_ch'], $ecommerceCh);
    }

    public function testUpdateChannelWhenValidationFailed()
    {
        $data =
<<<JSON
    {"code": "foo", "type":"bar"}
    {"code": "baz,","category_tree": "master", "currencies": ["EUR"], "locales": ["fr_FR"]}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"foo","status_code":422,"message":"Property \"type\" does not exist. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_channels__code_"}}}
{"line":2,"code":"baz,","status_code":422,"message":"Validation failed.","errors":[{"property":"code","message":"Channel code may contain only letters, numbers and underscores"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/channels', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * @return integer
     */
    protected function getBufferSize()
    {
        return $this->getParameter('api_input_buffer_size');
    }

    /**
     * @return integer
     */
    protected function getMaxNumberResources()
    {
        return $this->getParameter('api_input_max_resources_number');
    }

    /**
     * @param $code
     *
     * @return array
     */
    protected function getNormalizedChannel($code)
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($code);

        return $this->get('pim_catalog.normalizer.standard.channel')->normalize($channel);
    }
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
