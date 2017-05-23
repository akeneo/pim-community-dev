<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Security;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ChannelAuthorizationIntegration extends ApiTestCase
{
    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }
}
