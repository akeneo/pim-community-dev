<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Create;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class AddQuantifiedAssociationOnProductModelWithPermissionsEndToEnd extends ApiTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
        $this->loader->loadProductsForQuantifiedAssociationPermissions();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testAssociateProductModelWithGrantedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $data = <<<JSON
{
    "code": "my_product_model",
    "family_variant": "family_variant_permission",
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"identifier": "product_without_category", "quantity": 3},
                {"identifier": "product_viewable_by_everybody", "quantity": 4},
                {"identifier": "product_not_viewable_by_redactor", "quantity": 5}
            ]
        }
    }
}
JSON;
        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testAssociateProductModelWithGrantedProductModel()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = <<<JSON
{
    "code": "my_product_model",
    "family_variant": "family_variant_permission",
    "quantified_associations": {
        "PRODUCTSET": {
            "product_models": [
                {"identifier": "product_model_without_category", "quantity": 3},
                {"identifier": "product_model_viewable_by_everybody", "quantity": 4},
                {"identifier": "product_model_not_viewable_by_redactor", "quantity": 5}
            ]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testCannotAssociateProductWithNotGrantedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "code": "my_product_model",
    "family_variant": "family_variant_permission",
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"identifier": "product_not_viewable_by_redactor", "quantity": 3}
            ]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);
        $expectedResponseContent = <<<JSON
{"code":422,"message":"Validation failed.","errors":[{"property":"quantifiedAssociations.PRODUCTSET.products","message":"The following products don't exist: product_not_viewable_by_redactor. Please make sure the products haven't been deleted in the meantime."}]}
JSON;

        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }


    public function testCannotAssociateProductWithNotGrantedProductModel()
    {
        $data = <<<JSON
{
    "code": "my_product_model",
    "family_variant": "family_variant_permission",
    "quantified_associations": {
        "PRODUCTSET": {
            "product_models": [
                {"identifier": "product_model_not_viewable_by_redactor", "quantity": 4}
            ]
        }
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedResponseContent = <<<JSON
{"code":422,"message":"Validation failed.","errors":[{"property":"quantifiedAssociations.PRODUCTSET.product_models","message":"The following product models don't exist: product_model_not_viewable_by_redactor. Please make sure the product models haven't been deleted in the meantime."}]}
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }
}
