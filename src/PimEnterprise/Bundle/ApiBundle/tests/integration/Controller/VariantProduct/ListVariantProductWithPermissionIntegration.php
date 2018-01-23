<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PermissionFixturesLoader;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class ListVariantProductWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testGetListOfViewableVariantProducts()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?limit=20');
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $identifiers = array_map(function($item){
            return $item['identifier'];
        }, $content['_embedded']['items']);
        sort($identifiers);

        $expectedIdentifiers = [
            'colored_sized_shoes_view',
            'colored_sized_tshirt_view',
            'colored_sized_sweat_edit',
            'colored_sized_shoes_edit',
            'colored_sized_sweat_own',
            'colored_sized_shoes_own',
            'colored_sized_trousers'
        ];
        sort($expectedIdentifiers);
        Assert::assertSame($expectedIdentifiers, $identifiers);
    }

    /**
     * @fail
     */
    public function testGetListOfViewableAttributesAndLocaleValues()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products');
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"},
        "first": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"}
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/products/variant_product"
                    }
                },
                "identifier":"variant_product",
                "family": "family_permission",
                "parent":"sub_product_model",
                "categories":["own_category"],
                "enabled":true,
                "values":{
                    "root_product_model_edit_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ],
                    "root_product_model_view_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ],
                    "sub_product_model_edit_attribute":[
                        {"locale":"en_US", "scope":null, "data":true},
                        {"locale":"fr_FR", "scope":null, "data":true}
                    ],
                    "sub_product_model_view_attribute":[
                        {"locale":"en_US", "scope":null, "data":true},
                        {"locale":"fr_FR", "scope":null, "data":true}
                    ],
                    "variant_product_edit_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ],
                    "variant_product_view_attribute":[
                        {"locale":"en_US","scope":null,"data":true},
                        {"locale":"fr_FR","scope":null,"data":true}
                    ]
                },
                "created": "2016-06-14T13:12:50+02:00",
                "updated": "2016-06-14T13:12:50+02:00",
                "identifier": "variant_product",
                "groups": [],
                "associations": [],
                "metadata": {"workflow_status":"working_copy"}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @fail
     */
    public function testGetViewableAssociationsOnVariantProduct()
    {
        $this->loader->loadProductModelsForAssociationPermissions();
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products');

        $expected = <<<JSON
        {
            "_links": {
                "self": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"},
                "first": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"}
            },
            "current_page": 1,
            "_embedded": {
                "items": [
                    {
                        "_links": {
                            "self": {
                                "href": "http://localhost/api/rest/v1/products/product_view"
                            }
                        },
                        "identifier":"product_view",
                        "family":null,
                        "parent":null,
                        "categories":["view_category"],
                        "enabled":true,
                        "values":{},
                        "created": "2016-06-14T13:12:50+02:00",
                        "updated": "2016-06-14T13:12:50+02:00",
                        "groups": [],
                        "associations": {},
                        "metadata": {"workflow_status":"read_only"}
                    },
                    {
                        "_links": {
                            "self": {
                                "href": "http://localhost/api/rest/v1/products/product_own"
                            }
                        },
                        "identifier":"product_own",
                        "family":null,
                        "parent":null,
                        "categories":["own_category"],
                        "enabled":true,
                        "values":{},
                        "created": "2016-06-14T13:12:50+02:00",
                        "updated": "2016-06-14T13:12:50+02:00",
                        "groups": [],
                        "associations": {},
                        "metadata": {"workflow_status":"working_copy"}
                    },
                    {
                        "_links": {
                            "self": {
                                "href": "http://localhost/api/rest/v1/products/variant_product"
                            }
                        },
                        "identifier":"variant_product",
                        "family": "family_permission",
                        "parent":"sub_product_model",
                        "categories":["own_category"],
                        "enabled":true,
                        "values":{},
                        "created": "2016-06-14T13:12:50+02:00",
                        "updated": "2016-06-14T13:12:50+02:00",
                        "groups": [],
                        "associations": {
                            "PACK":{
                                "groups":[],
                                "products":[]
                            },
                            "SUBSTITUTION":{
                                "groups":[],
                                "products":[]
                            },
                            "UPSELL":{
                                "groups":[],
                                "products":[]
                            },
                            "X_SELL":{
                                "groups":[],
                                "products":["product_view"]
                            }
                        },
                        "metadata": {"workflow_status":"working_copy"}
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
