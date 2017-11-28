<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetCategory;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateAssetCategoryIntegration extends ApiTestCase
{
    public function testCreationOfAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
    {
        "code": "new_asset_category",
        "parent": "asset_main_catalog",
        "labels": {
            "en_US": "New asset category",
            "fr_FR": "Nouvelle catégorie d'asset",
            "de_DE": ""
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/asset-categories', [], [], [], $data);

        $category = $this->get('pimee_product_asset.repository.category')->findOneByIdentifier('new_asset_category');
        $categoryStandard = [
            'code'   => 'new_asset_category',
            'parent' => 'asset_main_catalog',
            'labels' => [
                'en_US' => 'New asset category',
                'fr_FR' => 'Nouvelle catégorie d\'asset'
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($categoryStandard, $normalizer->normalize($category));
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/asset-categories/new_asset_category', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent = <<<JSON
{
    "code": 400,
    "message": "Invalid json message received"
}
JSON;

        $client->request('POST', 'api/rest/v1/asset-categories', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenAssetCategoryCodeAlreadyExists()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "asset_main_catalog"
    }
JSON;

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "code",
            "message": "This value is already used."
        }
    ]
}
JSON;

        $client->request('POST', 'api/rest/v1/asset-categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenAPropertyIsNotExpected()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "sales",
        "extra_property": ""
    }
JSON;

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "Property \"extra_property\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#post_asset_categories"
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/asset-categories', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
