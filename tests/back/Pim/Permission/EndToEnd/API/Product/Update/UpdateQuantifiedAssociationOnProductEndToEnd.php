<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class UpdateQuantifiedAssociationOnProductEndToEnd extends ApiTestCase
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

    public function testAssociateProductWithGrantedProduct()
    {
        $data = <<<JSON
{
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

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testAssociateProductWithGrantedProductModel()
    {
        $data = <<<JSON
{
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

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testOnlyGrantedProductAndProductModelAreUpdated()
    {
        $data = <<<JSON
{
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"identifier": "product_viewable_by_everybody_1", "quantity": 2},
                {"identifier": "product_without_category", "quantity": 3}
            ],
            "product_models": [
                {"identifier": "product_model_viewable_by_everybody_1", "quantity": 1},
                {"identifier": "product_model_without_category", "quantity": 4}
            ]
        }
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_owned_by_redactor_and_associated_with_product_and_product_model', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        $expectedQuantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_not_viewable_by_redactor', 'quantity' => 1],
                    ['identifier' => 'product_viewable_by_everybody_1', 'quantity' => 2],
                    ['identifier' => 'product_without_category', 'quantity' => 3],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_not_viewable_by_redactor', 'quantity' => 4],
                    ['identifier' => 'product_model_viewable_by_everybody_1', 'quantity' => 1],
                    ['identifier' => 'product_model_without_category', 'quantity' => 4],
                ],
            ],
        ];

        $this->assertSameQuantifiedAssociation($expectedQuantifiedAssociations, 'product_owned_by_redactor_and_associated_with_product_and_product_model');
    }

    public function testCannotAssociateProductWithNotGrantedProduct()
    {
        $data = <<<JSON
{
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"identifier": "product_not_viewable_by_redactor", "quantity": 5}
            ]
        }
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], $data);

        $expectedContent = <<<JSON
{"code":422,"message":"Validation failed.","errors":[{"property":"quantifiedAssociations.PRODUCTSET.products","message":"The following products don't exist: product_not_viewable_by_redactor. Please make sure the products haven't been deleted in the meantime."}]}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
    }

    public function testAddProductModelNotGrantedInAssociation()
    {
        $data = <<<JSON
{
    "quantified_associations": {
        "PRODUCTSET": {
            "product_models": [
                {"identifier": "product_model_not_viewable_by_redactor", "quantity": 5}
            ]
        }
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], $data);

        $expectedContent = <<<JSON
{"code":422,"message":"Validation failed.","errors":[{"property":"quantifiedAssociations.PRODUCTSET.product_models","message":"The following product models don't exist: product_model_not_viewable_by_redactor. Please make sure the product models haven't been deleted in the meantime."}]}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
    }

    protected function assertSameQuantifiedAssociation(array $expectedQuantifiedAssociations, string $identifier)
    {
        $product = $this->get('pim_catalog.repository.product_without_permission')->findOneByIdentifier($identifier);

        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');
        $actualQuantifiedAssociations = $standardizedProduct['quantified_associations'];

        $this->assertSame($expectedQuantifiedAssociations, $actualQuantifiedAssociations);
    }
}
