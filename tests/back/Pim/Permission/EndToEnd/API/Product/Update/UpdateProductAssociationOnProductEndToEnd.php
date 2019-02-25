<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductAssociationOnProductEndToEnd extends AbstractProductTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testFailedToAssociateAProductNotGranted()
    {
        $this->createProduct('simple_product');
        $data = <<<JSON
{
    "associations": {
        "PACK": {
            "products": ["product_not_viewable_by_redactor"]
        }
    }
}
JSON;
        $expectedContent = <<<JSON
{"code":422,"message":"Property \"associations\" expects a valid product identifier. The product does not exist, \"product_not_viewable_by_redactor\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
JSON;
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/simple_product', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
    }

    public function testAddProductModelInAssociation()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = <<<JSON
{
    "associations": {
        "X_SELL": {
            "product_models": ["root_product_model"]
        }
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_own', [], [], [], $data);

        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    public function testAddProductModelNotGrantedInAssociation()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = <<<JSON
{
    "associations": {
        "X_SELL": {
            "product_models": ["product_model_no_view"]
        }
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_own', [], [], [], $data);
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{"code":422,"message":"Property \"associations\" expects a valid product model identifier. The product model does not exist, \"product_model_no_view\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
    }
}
