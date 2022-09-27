<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Create;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

class AddAssociationOnProductWithUuidAndPermissionsEndToEnd extends AbstractProductTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    public function testErrorProductWithNotGrantedAssociatedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $productNotViewableUuid = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor')->toString();
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}]
    },
    "associations": {
        "X_SELL": {
            "products": ["$productNotViewableUuid"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(sprintf(
            '{"code":422,"message":"Property \"associations\" expects a valid product uuid. The product does not exist, \"%s\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#post_products_uuid"}}}',
            $productNotViewableUuid
        ), $response->getContent());
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testSuccessProductWithGrantedAssociatedProductForManager()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $productNotViewableUuid = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor')->toString();
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}]
    },
    "associations": {
        "X_SELL": {
            "products": ["$productNotViewableUuid"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testSuccessProductWithViewableAssociatedProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $productViewableByEverybodyUuid = $this->getProductUuidFromIdentifier('product_viewable_by_everybody_2')->toString();
        $productWithoutCategoryUuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}]
    },
    "associations": {
        "X_SELL": {
            "products": ["$productWithoutCategoryUuid","$productViewableByEverybodyUuid"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testAddProductModelInAssociation()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "associate_with_product_model_view"}]
    },
    "associations": {
        "X_SELL": {
            "product_models": ["root_product_model"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $this->assertSame(201, $client->getResponse()->getStatusCode());
    }

    public function testAddProductModelNotGrantedInAssociation()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "associate_with_product_model_no_view"}]
    },
    "associations": {
        "X_SELL": {
            "product_models": ["product_model_no_view"]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();

        $expectedResponseContent = <<<JSON
{"code":422,"message":"Property \"associations\" expects a valid product model identifier. The product model does not exist, \"product_model_no_view\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#post_products_uuid"}}}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }
}
