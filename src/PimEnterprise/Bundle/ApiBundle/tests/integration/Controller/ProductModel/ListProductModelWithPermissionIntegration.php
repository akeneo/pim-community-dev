<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PermissionFixturesLoader;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class ListProductModelWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testGetListOfViewableProductModels()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/product-models?limit=20');
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $codes = array_map(function($item){
            return $item['code'];
        }, $content['_embedded']['items']);
        sort($codes);

        $expectedCodes = [
            'shoes_view',
            'tshirt_view',
            'sweat_edit',
            'shoes_own',
            'trousers',
            'colored_shoes_view',
            'colored_tshirt_view',
            'colored_sweat_edit',
            'colored_shoes_edit',
            'colored_jacket_own',
            'colored_shoes_own',
            'colored_trousers'
        ];
        sort($expectedCodes);
        Assert::assertSame($expectedCodes, $codes);
    }

    /**
     * @fail
     */
    public function testGetListOfViewableAttributesAndLocaleValues()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/product-models');
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=10"},
        "first": {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=10"}
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/product-models/root_product_model"
                    }
                },
                "code":"root_product_model",
                "family_variant":"family_variant_permission",
                "parent":null,
                "categories":["own_category"],
                "values":{
                    "root_product_model_edit_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ],
                    "root_product_model_view_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ]
                },
                "created": "2016-06-14T13:12:50+02:00",
                "updated": "2016-06-14T13:12:50+02:00"
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/product-models/sub_product_model"
                    }
                },
                "code":"sub_product_model",
                "family_variant":"family_variant_permission",
                "parent":"root_product_model",
                "categories":["own_category"],
                "values":{
                    "sub_product_model_view_attribute":[
                        {"locale":"en_US", "scope":null, "data":true},
                        {"locale":"fr_FR", "scope":null, "data":true}
                    ],
                    "sub_product_model_edit_attribute":[
                        {"locale":"en_US", "scope":null, "data":true},
                        {"locale":"fr_FR", "scope":null, "data":true}
                    ],
                    "root_product_model_edit_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ],
                    "root_product_model_view_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ]
                },
                "created": "2016-06-14T13:12:50+02:00",
                "updated": "2016-06-14T13:12:50+02:00"
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        if (!isset($result['_embedded'])) {
            \PHPUnit_Framework_Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
