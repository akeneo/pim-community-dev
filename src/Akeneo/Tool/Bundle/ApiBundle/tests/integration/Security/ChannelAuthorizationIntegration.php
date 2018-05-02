<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Security;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ChannelAuthorizationIntegration extends ApiTestCase
{
    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'kevin', 'kevin');

        $client->request('GET', '/api/rest/v1/channels');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "You are not allowed to access the web API."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForListingChannels()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/channels');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingChannels()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/channels');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list channels."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForGettingAChannel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/channels/ecommerce');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForGettingAChannel()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/channels/ecommerce');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list channels."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForCreatingAChannel()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code":"mobile",
    "locales": ["fr_FR"],
    "currencies": ["EUR"],
    "category_tree": "master"
}
JSON;

        $client->request('POST', '/api/rest/v1/channels', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForCreatingAChannel()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = <<<JSON
{
    "code":"ecommerce"
}
JSON;

        $client->request('POST', '/api/rest/v1/channels', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update channels."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingAChannel()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingAChannel()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/channels/ecommerce', [], [], [], $data);

        $expectedResponse =
<<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update channels."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingAListOfChannel()
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data =
<<<JSON
{"code": "ecommerce","currencies": ["EUR"]}
{"code": "ecommerce_china","currencies": ["EUR"]}
JSON;

        ob_start(function() { return ''; });
        $client->request('PATCH', '/api/rest/v1/channels', [], [], [], $data);
        ob_end_flush();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingAListOfChannel()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data =
<<<JSON
{"code": "ecommerce","currencies": ["EUR"]}
{"code": "ecommerce_china","currencies": ["EUR"]}
JSON;

        $client->request('PATCH', '/api/rest/v1/channels', [], [], [], $data);

        $expectedResponse =
<<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update channels."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
