<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetCategory;

use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetAssetCategoryIntegration extends ApiTestCase
{
    public function testGetACompleteAssetCategory()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/asset-categories/asset_main_catalog');

        $standardCategoryAsset = <<<JSON
{
    "code": "asset_main_catalog",
    "parent": null,
    "labels": {
        "en_US": "Asset main catalog"
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardCategoryAsset, $response->getContent());
    }

    /**
     * TODO: should be an integration test
     */
    public function testNotFoundAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/asset-categories/not_found');

        $expectedResponse = <<<JSON
{
    "code":404,
    "message":"Asset category \"not_found\" does not exist."
}
JSON;


        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
