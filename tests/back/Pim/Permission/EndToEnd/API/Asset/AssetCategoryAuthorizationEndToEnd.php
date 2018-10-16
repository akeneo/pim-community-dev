<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Asset;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AssetCategoryAuthorizationEndToEnd extends ApiTestCase
{
    /**
     * Should be an integration test.
     */
    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'kevin', 'kevin');

        $client->request('GET', '/api/rest/v1/asset-categories/asset_main_catalog');

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

    /**
     * Should be an integration test.
     */
    public function testAccessGrantedForListingAssetCategories()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/asset-categories');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessDeniedForListingAssetCategories()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/asset-categories');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list asset categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessGrantedForGettingAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/asset-categories/asset_main_catalog');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessDeniedForGettingAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/asset-categories/asset_main_catalog');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list asset categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForCreatingAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
{
    "code": "new_category"
}
JSON;

        $client->request('POST', '/api/rest/v1/asset-categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testAccessDeniedForCreatingAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = <<<JSON
{
    "code": "super_new_category"
}
JSON;

        $client->request('POST', '/api/rest/v1/asset-categories', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update asset categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/asset-categories/asset_main_catalog', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = '{}';

        $client->request('PATCH', '/api/rest/v1/asset-categories/asset_main_catalog', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update asset categories."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForPartialUpdatingAListOfAssetCategories()
    {
        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
{"code": "a_category"}
JSON;

        ob_start(function() { return ''; });
        $client->request('PATCH', '/api/rest/v1/asset-categories', [], [], [], $data);
        ob_end_flush();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForPartialUpdatingAListOfAssetCategories()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);

        $data = <<<JSON
{"code": "a_category"}
JSON;

        $client->request('PATCH', '/api/rest/v1/asset-categories', [], [], [], $data);

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update asset categories."
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
