<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Security;

use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AssetCategoryAuthorizationIntegration extends ApiTestCase
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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
