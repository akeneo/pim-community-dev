<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetCategory;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PimEnterprise\Component\ProductAsset\Model\Category;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateAssetCategoryIntegration extends ApiTestCase
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

        $client->request('PATCH', 'api/rest/v1/asset-categories/new_asset_category', [], [], [], $data);

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

    public function testPartialUpdateOfAnAssetCategory()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "asset_main_catalog",
                "labels": {
                    "en_US": null,
                    "fr_FR": "Principale catalogue d'asset"
                }
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/asset-categories/asset_main_catalog', [], [], [], $data);

        $this->get('doctrine.orm.default_entity_manager')->clear();
        $category = $this->get('pimee_product_asset.repository.category')->findOneByIdentifier('asset_main_catalog');
        $categoryStandard = [
            'code'   => 'asset_main_catalog',
            'parent' => null,
            'labels' => [
                'fr_FR' => 'Principale catalogue d\'asset',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($categoryStandard, $normalizer->normalize($category));
    }

    /**
     * Should be an integration test.
     */
    public function testCreationOfACategoryWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/asset-categories/new_category_empty_content', [], [], [], $data);

        $category = $this->get('pimee_product_asset.repository.category')->findOneByIdentifier('new_category_empty_content');
        $categoryStandard = [
            'code'   => 'new_category_empty_content',
            'parent' => null,
            'labels' => [],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($categoryStandard, $normalizer->normalize($category));
    }

    /**
     * Should be an integration test.
     */
    public function testPartialUpdateWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/asset-categories/asset_main_catalog', [], [], [], $data);

        $category = $this->get('pimee_product_asset.repository.category')->findOneByIdentifier('asset_main_catalog');
        $categoryStandard = [
            'code'   => 'asset_main_catalog',
            'parent' => null,
            'labels' => [
                'en_US' => 'Asset main catalog',
            ],
        ];
        $normalizer = $this->get('pim_catalog.normalizer.standard.category');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($categoryStandard, $normalizer->normalize($category));
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenContentIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{';

        $expectedContent = <<<JSON
            {
                "code": 400,
                "message": "Invalid json message received"
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/asset-categories/new_asset_category', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "new_code",
                "labels": {
                    "foo" : "label"
                 }
            }
JSON;

        $expectedContent = <<<JSON
            {
                "code": 422,
                "message": "Validation failed.",
                "errors": [
                    {
                        "property": "code",
                        "message": "This property cannot be changed."
                    },
                    {
                        "property": "labels",
                        "message": "The locale \"foo\" does not exist."
                    }
                ]
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/asset-categories/asset_main_catalog', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenUpdateOfAssetCategoryFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "extra_property": ""
            }
JSON;

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "Property \"extra_property\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_asset_categories__code_"
        }
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/asset-categories/asset_main_catalog', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenACategoryIsCreatedWithInconsistentCodes()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "inconsistent_code2"
            }
JSON;

        $expectedContent = <<<JSON
            {
                "code": 422,
                "message": "The code \"inconsistent_code2\" provided in the request body must match the code \"inconsistent_code1\" provided in the url."
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/asset-categories/inconsistent_code1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenParentIsMovedInChildren()
    {
        $childCategory = new Category();
        $this->get('pimee_product_asset.updater.category')->update($childCategory, [
            'code' => 'child_category',
            'parent' => 'asset_main_catalog'
        ]);
        static::assertCount(0, $this->get('validator')->validate($childCategory));
        $this->get('pimee_product_asset.saver.category')->save($childCategory);

        $client = $this->createAuthenticatedClient();
        $parentCategoryId = $this->get('pimee_product_asset.repository.category')->findOneByIdentifier('asset_main_catalog')->getId();

        $data = '{"parent": "child_category"}';
        $expectedContent = sprintf('{"code":422, "message": "Cannot set child as parent to node: %d"}', $parentCategoryId);
        $client->request('PATCH', 'api/rest/v1/asset-categories/asset_main_catalog', [], [], [], $data);

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
