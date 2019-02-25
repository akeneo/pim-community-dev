<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductModel;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class GetVariantProductWithPermissionEndToEnd extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testGetNotViewableVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'Product "%s" does not exist.';
        $this->assertUnauthorized('colored_sized_sweat_no_view', sprintf($message, 'colored_sized_sweat_no_view'));
    }

    public function testGetViewableVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $this->assertAuthorized('colored_sized_shoes_view');
        $this->assertAuthorized('colored_sized_tshirt_view');
        $this->assertAuthorized('colored_sized_tshirt_view');
        $this->assertAuthorized('colored_sized_sweat_edit');
        $this->assertAuthorized('colored_sized_shoes_edit');
        $this->assertAuthorized('colored_sized_sweat_own');
        $this->assertAuthorized('colored_sized_shoes_own');
        $this->assertAuthorized('colored_sized_trousers');
    }

    public function testGetViewableAttributesAndLocaleOnVariantProduct()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/variant_product');

        $expected = <<<JSON
        {
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
                "sub_product_model_axis_attribute":[
                    {"locale":null, "scope":null, "data":true}
                ],
                "sub_product_model_view_attribute":[
                    {"locale":"en_US", "scope":null, "data":true},
                    {"locale":"fr_FR", "scope":null, "data":true}
                ],
                "variant_product_axis_attribute":[
                    {"locale":null, "scope":null, "data":true}
                ],
                "variant_product_edit_attribute":[
                    {"locale":"en_US","scope":null,"data":true},
                    {"locale":"fr_FR","scope":null,"data":true},
                    {"locale":"zh_CN", "scope":null, "data":false}
                ],
                "variant_product_view_attribute":[
                    {"locale":"en_US","scope":null,"data":true},
                    {"locale":"fr_FR","scope":null,"data":true},
                    {"locale":"zh_CN", "scope":null, "data":false}
                ]
            },
            "created": "2016-06-14T13:12:50+02:00",
            "updated": "2016-06-14T13:12:50+02:00",
            "identifier": "variant_product",
            "groups": [],
            "associations": [],
            "metadata": {"workflow_status":"working_copy"}
        }
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expected);
    }

    public function testGetViewableAssociationsOnVariantProduct()
    {
        $this->loader->loadProductsForAssociationPermissions();
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/variant_product');

        $expected = <<<JSON
        {
            "identifier":"variant_product",
            "family": "family_permission",
            "parent":"sub_product_model",
            "categories":["own_category"],
            "enabled":true,
            "values":{
               "variant_product_axis_attribute":[
                    {"locale":null, "scope":null, "data":true}
                ],
                "sub_product_model_axis_attribute":[
                    {"locale":null, "scope":null, "data":true}
                ],
                "variant_product_edit_attribute":[
                    {"locale":"en_US","scope":null,"data":false},
                    {"locale":"fr_FR","scope":null,"data":false},
                    {"locale":"zh_CN", "scope":null, "data":false}
                ],
                "variant_product_view_attribute":[
                    {"locale":"en_US","scope":null,"data":false},
                    {"locale":"fr_FR","scope":null,"data":false},
                    {"locale":"zh_CN", "scope":null, "data":false}
                ]
            },
            "created": "2016-06-14T13:12:50+02:00",
            "updated": "2016-06-14T13:12:50+02:00",
            "groups": [],
            "associations": {
                "PACK":{
                    "groups":[],
                    "products":[],
                    "product_models":[]
                },
                "SUBSTITUTION":{
                    "groups":[],
                    "products":[],
                    "product_models":[]
                },
                "UPSELL":{
                    "groups":[],
                    "products":[],
                    "product_models":[]
                },
                "X_SELL":{
                    "groups":[],
                    "products":["product_view"],
                    "product_models":[]
                }
            },
            "metadata": {"workflow_status":"working_copy"}
        }
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expected);
    }

    /**
     * @param string $code
     * @param string $message
     */
    private function assertUnauthorized(string $code, string $message)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/' . $code);
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', Response::HTTP_NOT_FOUND, addslashes($message));

        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        Assert::assertEquals($expected, $response->getContent());
    }

    /**
     * @param string $code
     */
    private function assertAuthorized(string $code)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/' . $code);
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    private function assertResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);
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
