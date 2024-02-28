<?php

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/* TODO: (TO FIX) Test not executed: this class file's name should end with "EndToEnd" to be executed.
   TODO: But half the tests get a 200 from external api when it should get a 403 Access Forbidden. */
class CategoryAuthorizationEndToEnd extends ApiTestCase
{
    public function testOverallAccessDenied(): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'kevin', 'kevin');

        $client->request('GET', '/api/rest/v1/categories');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "You are not allowed to access the web API."
}
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForListingCategories(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/categories');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingCategories(): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/categories');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForGettingACategory(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/categories/master');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForGettingACategory(): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/categories/master');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForCreatingACategory(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "new_category"
}
JSON;

        $client->request('POST', '/api/rest/v1/categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testAccessDeniedForCreatingACategory(): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = <<<JSON
{
    "code": "super_new_category"
}
JSON;

        $client->request('POST', '/api/rest/v1/categories', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingACategory(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/categories/master', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testAccessDeniedForPartialUpdatingACategory(): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/categories/master', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingAListOfCategories(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
{"code": "a_category"}
JSON;

        ob_start(function () {
            return '';
        });
        $client->request('PATCH', '/api/rest/v1/categories', [], [], [], $data);
        ob_end_flush();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingAListOfCategories(): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
{"code": "a_category"}
JSON;
        ob_start(function () {
            return '';
        });
        $client->request('PATCH', '/api/rest/v1/categories', [], [], [], $data);
        ob_end_flush();

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
