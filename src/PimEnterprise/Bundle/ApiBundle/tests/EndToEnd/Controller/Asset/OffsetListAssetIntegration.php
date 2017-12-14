<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetCategory;

use Akeneo\Test\Integration\Configuration;
use PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset\AbstractAssetTestCase;
use Symfony\Component\HttpFoundation\Response;

class OffsetListAssetIntegration extends AbstractAssetTestCase
{
    /**
     * Should be an integration test.
     */
    public function testListAssetsWithoutLimitAndWithCount()
    {
        $assets = $this->getStandardizedAssetsWithLinks();

        $expected = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/assets?page=1&with_count=true&pagination_type=page&limit=10"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?page=1&with_count=true&pagination_type=page&limit=10"
		}
	},
	"current_page": 1,
	"items_count": 6,
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

        $this->assert('api/rest/v1/assets?with_count=true', $expected);
    }

    public function testIterationOnListOfAssetsWithLimitAndWithoutCount()
    {
        $assets = $this->getStandardizedAssetsWithLinks();

        $expectedPage1 = <<<JSON
{
	"_links": {
		"self": {
			"href": "http://localhost/api/rest/v1/assets?page=1&with_count=false&pagination_type=page&limit=2"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?page=1&with_count=false&pagination_type=page&limit=2"
		},
        "next": {
		    "href": "http://localhost/api/rest/v1/assets?page=2&with_count=false&pagination_type=page&limit=2"
		}
	},
	"current_page": 1,
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
			"href": "http://localhost/api/rest/v1/assets?page=2&with_count=false&pagination_type=page&limit=2"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?page=1&with_count=false&pagination_type=page&limit=2"
		},
        "next": {
		    "href": "http://localhost/api/rest/v1/assets?page=3&with_count=false&pagination_type=page&limit=2"
		},
        "previous": {
		    "href": "http://localhost/api/rest/v1/assets?page=1&with_count=false&pagination_type=page&limit=2"
		}
	},
	"current_page": 2,
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
			"href": "http://localhost/api/rest/v1/assets?page=3&with_count=false&pagination_type=page&limit=2"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?page=1&with_count=false&pagination_type=page&limit=2"
		},
        "next": {
		    "href": "http://localhost/api/rest/v1/assets?page=4&with_count=false&pagination_type=page&limit=2"
		},
        "previous": {
		    "href": "http://localhost/api/rest/v1/assets?page=2&with_count=false&pagination_type=page&limit=2"
		}
	},
	"current_page": 3,
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
			"href": "http://localhost/api/rest/v1/assets?page=4&with_count=false&pagination_type=page&limit=2"
		},
		"first": {
			"href": "http://localhost/api/rest/v1/assets?page=1&with_count=false&pagination_type=page&limit=2"
		},
        "previous": {
		    "href": "http://localhost/api/rest/v1/assets?page=3&with_count=false&pagination_type=page&limit=2"
		}
	},
	"current_page": 4,
	"_embedded": {
		"items": []
	}
}
JSON;

        $this->assert('api/rest/v1/assets?pagination_type=page&limit=2', $expectedPage1);
        $this->assert('api/rest/v1/assets?pagination_type=page&limit=2&page=2', $expectedPage2);
        $this->assert('api/rest/v1/assets?pagination_type=page&limit=2&page=3', $expectedPage3);
        $this->assert('api/rest/v1/assets?pagination_type=page&limit=2&page=4', $expectedPage4);
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
