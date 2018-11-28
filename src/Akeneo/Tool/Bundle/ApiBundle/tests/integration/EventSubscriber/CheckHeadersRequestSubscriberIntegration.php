<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CheckHeadersRequestSubscriberIntegration extends ApiTestCase
{
    public function testErrorIfAcceptHeaderIsXml()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/master', [], [], ['HTTP_ACCEPT' => 'application/xml']);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $response->getStatusCode(), 'Header is not acceptable');
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'Error response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $content['code']);
        $this->assertSame('"application/xml" in "Accept" header is not valid. Only "application/json" is allowed.', $content['message']);
    }

    public function testSuccessIfAcceptHeaderIsJson()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/master', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testSuccessIfAcceptHeaderIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/master');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testErrorIfContentTypeHeaderIsXml()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/categories', [], [], [
            'CONTENT_TYPE' => 'application/xml',
        ], '{"code": "my_category"}');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode(), 'Header is not acceptable');
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'Error response contains 2 items');
        $this->assertSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $content['code']);
        $this->assertSame('"application/xml" in "Content-Type" header is not valid. Only "application/json" is allowed.', $content['message']);
    }

    public function testSuccessIfContentTypeHeaderIsJson()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', 'api/rest/v1/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"code": "my_category"}');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testErrorIfContentTypeHeaderIsJsonOnListPatch()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('PATCH', 'api/rest/v1/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"code": "my_category"}');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode(), 'Header is not acceptable');
        $expected = <<<JSON
{
    "code": 415,
    "message": "\"application/json\" in \"Content-Type\" header is not valid. Only \"application/vnd.akeneo.collection+json\" is allowed."
}
JSON;

        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testSuccessIfContentTypeHeaderIsVndOnListPatch()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('PATCH', 'api/rest/v1/categories', [], [], [
            'CONTENT_TYPE' => 'application/vnd.akeneo.collection+json',
        ]);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Header is acceptable');
    }

    public function testSuccessIfContentTypeHeaderIsJsonOnPatch()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('PATCH', 'api/rest/v1/categories/master', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"code": "master"}');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), 'Header is acceptable');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
