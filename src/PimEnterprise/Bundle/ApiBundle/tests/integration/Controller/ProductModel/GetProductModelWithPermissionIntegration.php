<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PermissionFixturesLoader;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetProductModelWithPermissionIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testGetNotViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $message = 'Product model "%s" does not exist.';
        $this->assertUnauthorized('sweat_no_view', sprintf($message, 'sweat_no_view'));
        $this->assertUnauthorized('colored_sweat_no_view', sprintf($message, 'colored_sweat_no_view'));
        $this->assertUnauthorized('shoes_no_view', sprintf($message, 'shoes_no_view'));
        $this->assertUnauthorized('jacket_no_view', sprintf($message, 'jacket_no_view'));
    }

    public function testGetViewableProductModel()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $this->assertAuthorized('shoes_view');
        $this->assertAuthorized('tshirt_view');
        $this->assertAuthorized('sweat_edit');
        $this->assertAuthorized('shoes_own');
        $this->assertAuthorized('trousers');
        $this->assertAuthorized('colored_shoes_view');
        $this->assertAuthorized('colored_tshirt_view');
        $this->assertAuthorized('colored_sweat_edit');
        $this->assertAuthorized('colored_shoes_edit');
        $this->assertAuthorized('colored_shoes_own');
        $this->assertAuthorized('colored_trousers');
    }

    public function testGetViewableAttributesAndLocaleOnRootProductModel()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = '{"categories": ["own_category"]}';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/product-models/root_product_model', [], [], [], $data);

        $expected = <<<JSON
        {
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
        }
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expected);
    }

    /**
     * @fail
     */
    public function testGetViewableAttributesAndLocaleOnSubProductModel()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $data = '{"categories": ["own_category"]}';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/product-models/sub_product_model', [], [], [], $data);

        $expected = <<<JSON
        {
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

        $client->request('GET', 'api/rest/v1/product-models/' . $code);
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

        $client->request('GET', 'api/rest/v1/product-models/' . $code);
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
