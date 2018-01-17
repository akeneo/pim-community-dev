<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PermissionFixturesLoader;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetVariantProductWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
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

    /**
     * @fail
     */
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
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expected);
    }

    /**
     * @fail
     */
    public function testGetViewableAssociationsOnVariantProduct()
    {
        $this->loader->loadProductModelsForAssociationPermissions();
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
