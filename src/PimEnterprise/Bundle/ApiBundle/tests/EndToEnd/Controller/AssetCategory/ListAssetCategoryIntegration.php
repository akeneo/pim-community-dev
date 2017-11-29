<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetCategory;

use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PimEnterprise\Component\ProductAsset\Model\Category;
use Symfony\Component\HttpFoundation\Response;

class ListAssetCategoryIntegration extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $assetCategory = new Category();
        $this->get('pimee_product_asset.updater.category')->update($assetCategory, [
            'code' => 'category_asset_1',
            'parent' => 'asset_main_catalog'
        ]);
        static::assertCount(0, $this->get('validator')->validate($assetCategory));

        $this->get('pimee_product_asset.saver.category')->save($assetCategory);
    }

    public function testListAssetCategories()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/asset-categories');

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/asset-categories?page=1&limit=10&with_count=false"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/asset-categories?page=1&limit=10&with_count=false"
		}
	},
	"current_page": 1,
	"_embedded": {
		"items": [{
			"_links": {
				"self": {
					"href": "http://localhost/api/rest/v1/asset-categories/asset_main_catalog"
				}
			},
			"code": "asset_main_catalog",
			"parent": null,
			"labels": {
			    "en_US": "Asset main catalog"
			}
		}, {
			"_links": {
				"self": {
					"href": "http://localhost/api/rest/v1/asset-categories/category_asset_1"
				}
			},
			"code": "category_asset_1",
			"parent": "asset_main_catalog",
			"labels": {}
		}]
	}
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * TODO: should be an integration test
     */
    public function testListAssetCategoriesWithCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/asset-categories?with_count=true');

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/asset-categories?page=1&limit=10&with_count=true"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/asset-categories?page=1&limit=10&with_count=true"
		}
	},
	"current_page": 1,
	"items_count": 2,
	"_embedded": {
		"items": [{
			"_links": {
				"self": {
					"href": "http://localhost/api/rest/v1/asset-categories/asset_main_catalog"
				}
			},
			"code": "asset_main_catalog",
			"parent": null,
			"labels": {
			    "en_US": "Asset main catalog"
			}
		}, {
			"_links": {
				"self": {
					"href": "http://localhost/api/rest/v1/asset-categories/category_asset_1"
				}
			},
			"code": "category_asset_1",
			"parent": "asset_main_catalog",
			"labels": {}
		}]
	}
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * TODO: should be an integration test
     */
    public function testOutOfRangeListAssetCategories()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/asset-categories?limit=10&page=2');


        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/asset-categories?page=2&limit=10&with_count=false"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/asset-categories?page=1&limit=10&with_count=false"
        },
        "previous": {
            "href": "http://localhost/api/rest/v1/asset-categories?page=1&limit=10&with_count=false"
        }
    },
    "current_page": 2,
    "_embedded": {
        "items": []
    }
}
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
