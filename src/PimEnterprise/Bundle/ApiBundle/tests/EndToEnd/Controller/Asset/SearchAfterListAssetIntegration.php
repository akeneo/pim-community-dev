<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetCategory;

use Akeneo\Test\Integration\Configuration;
use PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset\AbstractAssetTestCase;
use Symfony\Component\HttpFoundation\Response;

class SearchAfterListAssetIntegration extends AbstractAssetTestCase
{
    /**
     * Should be an integration test.
     */
    public function testListAssetsWithoutLimit()
    {
        $assets = $this->getStandardizedAssetsWithLinks();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=10"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=10"
		}
	},
	"_embedded": {
		"items": [
		    ${assets['cat']},
		    ${assets['dog']},
		    ${assets['localizable_asset']},
		    ${assets['localizable_asset_without_references']},
		    ${assets['non_localizable_asset']},
		    ${assets['non_localizable_asset_without_references']}
		]
	}
}
JSON;

        $this->assert('api/rest/v1/assets?pagination_type=search_after', $expected);
    }

    public function testIterationOnListOfAssetsWithALimit()
    {
        $assets = $this->getStandardizedAssetsWithLinks();

        $expectedPage1 = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2"
		},
        "next": {
		    "href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=dog"
		}
	},
	"_embedded": {
		"items": [
		    ${assets['cat']},
		    ${assets['dog']}
		]
	}
}
JSON;

        $expectedPage2 = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=dog"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2"
		},
        "next": {
		    "href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=localizable_asset_without_references"
		}
	},
	"_embedded": {
		"items": [
		    ${assets['localizable_asset']},
		    ${assets['localizable_asset_without_references']}
		]
	}
}
JSON;

        $expectedPage3 = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=localizable_asset_without_references"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2"
		},
        "next": {
		    "href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=non_localizable_asset_without_references"
		}
	},
	"_embedded": {
		"items": [
		    ${assets['non_localizable_asset']},
		    ${assets['non_localizable_asset_without_references']}
		]
	}
}
JSON;

        $expectedPage4 = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=non_localizable_asset_without_references"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?pagination_type=search_after&limit=2"
		}
	},
	"_embedded": {
		"items": []
	}
}
JSON;

        $this->assert('api/rest/v1/assets?pagination_type=search_after&limit=2', $expectedPage1);
        $this->assert('api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=dog', $expectedPage2);
        $this->assert('api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=localizable_asset_without_references', $expectedPage3);
        $this->assert('api/rest/v1/assets?pagination_type=search_after&limit=2&search_after=non_localizable_asset_without_references', $expectedPage4);
    }

    /**
     * Should be an integration test.
     */
    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/assets?pagination_type=unknown');

        $response = $client->getResponse();

        $expected = '{"code": 422,"message":"Pagination type does not exist."}';

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    protected function getStandardizedAssetsWithLinks(): array
    {
        $assets = $this->getStandardizedAssets();
        $assetsWithLinks = [];
        foreach ($assets as $code => $jsonAsset) {
            $asset = json_decode($jsonAsset, true);
            $asset['_links']['self']['href'] = sprintf('http://localhost/api/rest/v1/assets/%s', $code);
            $assetsWithLinks[$code] = json_encode($asset);
        }

        return $assetsWithLinks;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $url
     * @param string $expected
     */
    private function assert(string $url, string $expected)
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
