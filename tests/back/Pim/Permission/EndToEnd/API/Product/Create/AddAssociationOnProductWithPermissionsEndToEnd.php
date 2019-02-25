<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Create;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class AddAssociationOnProductWithPermissionsEndToEnd extends AbstractProductTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testErrorProductWithNotGrantedAssociatedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "associations": {
        "X_SELL": {
            "products": ["product_not_viewable_by_redactor"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('{"code":422,"message":"Property \"associations\" expects a valid product identifier. The product does not exist, \"product_not_viewable_by_redactor\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#post_products"}}}', $response->getContent());
    }

    public function testSuccessProductWithGrantedAssociatedProductForManager()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $data = <<<JSON
{
    "identifier": "my_product",
    "associations": {
        "X_SELL": {
            "products": ["product_not_viewable_by_redactor"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testSuccessProductWithViewableAssociatedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "associations": {
        "X_SELL": {
            "products": ["product_without_category","product_viewable_by_everybody_2"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testAddProductModelInAssociation()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = <<<JSON
{
    "identifier": "associate_with_product_model_view",
    "associations": {
        "X_SELL": {
            "product_models": ["root_product_model"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $this->assertSame(201, $client->getResponse()->getStatusCode());
    }

    public function testAddProductModelNotGrantedInAssociation()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = <<<JSON
{
    "identifier": "associate_with_product_model_no_view",
    "associations": {
        "X_SELL": {
            "product_models": ["product_model_no_view"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();

        $expectedResponseContent = <<<JSON
{"code":422,"message":"Property \"associations\" expects a valid product model identifier. The product model does not exist, \"product_model_no_view\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#post_products"}}}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }
}
