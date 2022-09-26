<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductAssociationOnProductWithUuidEndToEnd extends AbstractProductTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    public function testFailedToAssociateAProductNotGranted()
    {
        $this->createProduct('simple_product');
        $productNotViewableByRedactor = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor')->toString();
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "simple_product"}]
    },
    "associations": {
        "PACK": {
            "products": ["{$productNotViewableByRedactor}"]
        }
    }
}
JSON;
        $expectedContent = <<<JSON
{"code":422,"message":"Property \"associations\" expects a valid product uuid. The product does not exist, \"{$productNotViewableByRedactor}\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products_uuid__uuid_"}}}
JSON;
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

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
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_own"}]
    },
    "associations": {
        "X_SELL": {
            "product_models": ["root_product_model"]
        }
    }
}
JSON;

        $uuid = $this->getProductUuidFromIdentifier('product_own')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

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

        $uuid = $this->getProductUuidFromIdentifier('product_own')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{"code":422,"message":"Property \"associations\" expects a valid product model identifier. The product model does not exist, \"product_model_no_view\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products_uuid__uuid_"}}}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
    }
}
