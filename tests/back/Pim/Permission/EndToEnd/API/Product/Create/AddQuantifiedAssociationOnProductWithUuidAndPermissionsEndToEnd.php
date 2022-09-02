<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Create;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

class AddQuantifiedAssociationOnProductWithUuidAndPermissionsEndToEnd extends ApiTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    public function testAssociateProductWithGrantedProduct()
    {
        $this->loader->loadProductsForQuantifiedAssociationPermissions();
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $productWithoutCategoryUuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $productViewableByEverybodyUuid = $this->getProductUuidFromIdentifier('product_viewable_by_everybody')->toString();
        $productNotViewableByRedactor = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor')->toString();

        $data = <<<JSON
{
    "values": {
        "sku": [{"scope": null, "locale": null, "data": "my_product"}]
    },
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"uuid": "$productWithoutCategoryUuid", "quantity": 3},
                {"uuid": "$productViewableByEverybodyUuid", "quantity": 4},
                {"uuid": "$productNotViewableByRedactor", "quantity": 5}
            ]
        }
    }
}
JSON;
        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testAssociateProductWithGrantedProductModel()
    {
        $this->loader->loadProductsForQuantifiedAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $data = <<<JSON
{
    "values": {
        "sku": [{"scope": null, "locale": null, "data": "my_product"}]
    },
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

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testCannotAssociateProductWithNotGrantedProduct()
    {
        $this->loader->loadProductsForQuantifiedAssociationPermissions();

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $productNotViewableByRedactor = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor')->toString();

        $data = <<<JSON
{
    "values": {
        "sku": [{"scope": null, "locale": null, "data": "my_product"}]
    },
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"uuid": "$productNotViewableByRedactor", "quantity": 3}
            ]
        }
    }
}
JSON;

        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedResponseContent = <<<JSON
{"code":422,"message":"Validation failed.","errors":[{"property":"quantifiedAssociations.PRODUCTSET.products","message":"The following products don't exist: $productNotViewableByRedactor. Please make sure the products haven't been deleted in the meantime."}]}
JSON;

        $response = $client->getResponse();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }


    public function testCannotAssociateProductWithNotGrantedProductModel()
    {
        $this->loader->loadProductsForQuantifiedAssociationPermissions();

        $data = <<<JSON
{
    "values": {
        "sku": [{"scope": null, "locale": null, "data": "my_product"}]
    },
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
        $client->request('POST', 'api/rest/v1/products-uuid', [], [], [], $data);

        $expectedResponseContent = <<<JSON
{"code":422,"message":"Validation failed.","errors":[{"property":"quantifiedAssociations.PRODUCTSET.product_models","message":"The following product models don't exist: product_model_not_viewable_by_redactor. Please make sure the product models haven't been deleted in the meantime."}]}
JSON;
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    private function getProductUuidFromIdentifier(string $productIdentifier): UuidInterface
    {
        return Uuid::fromString($this->get('database_connection')->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?', [$productIdentifier]
        ));
    }
}
