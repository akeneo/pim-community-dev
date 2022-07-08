<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product;

use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-----------------------------------------------+
 * |          |                   Categories                  |
 * +  Roles   +-----------------------------------------------+
 * |          |   categoryA   |   categoryA1  |   categoryB   |
 * +----------+-----------------------------------------------+
 * | Redactor |   View,Edit   |     View      |               |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit,Own |
 * +----------+-----------------------------------------------+
 */
class DeleteProductEndToEnd extends AbstractProductTestCase
{
    public function testDeleteProductSuccessful()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('DELETE', 'api/rest/v1/products/product_viewable_by_everybody_1');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
        $this->assertProductDeleted('product_viewable_by_everybody_1');
    }

    public function testDeleteProductByUuidSuccessful()
    {
        $product = $this->createProduct('test_identifier', []);
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . $product->getUuid()->toString());

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
        $this->assertProductDeleted('test_identifier');
    }

    public function testDeleteProductWithoutCategory()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products/product_without_category');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
        $this->assertProductDeleted('product_without_category');
    }

    public function testProductNotDeletableByUserWhoCanEditBuIsNotOwner()
    {
        $this->createProduct('product_not_owned_by_redactor', [
            'categories' => ['categoryA']
        ]);

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You can delete a product only if it is classified in at least one category on which you have an own permission."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products/product_not_owned_by_redactor');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_not_owned_by_redactor');
    }

    public function testProductNotDeletableByUuidByUserWhoCanEditButIsNotOwner()
    {
        $product = $this->createProduct('product_not_owned_by_redactor', [
            'categories' => ['categoryA']
        ]);

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You can delete a product only if it is classified in at least one category on which you have an own permission."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . $product->getUuid()->toString());

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_not_owned_by_redactor');
    }

    public function testProductNotDeletableByUserWhoCanOnlyViewProduct()
    {
        $this->createProduct('product_not_owned_by_redactor', [
            'categories' => ['categoryA1']
        ]);

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You can delete a product only if it is classified in at least one category on which you have an own permission."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products/product_not_owned_by_redactor');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_not_owned_by_redactor');
    }

    public function testProductNotDeletableByUuidByUserWhoCanOnlyViewProduct()
    {
        $product = $this->createProduct('product_not_owned_by_redactor', [
            'categories' => ['categoryA1']
        ]);

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You can delete a product only if it is classified in at least one category on which you have an own permission."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . $product->getUuid()->toString());

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_not_owned_by_redactor');
    }

    public function testProductNotDeletableByUserWhoHasNoPermissions()
    {
        $expectedResponseContent =
<<<JSON
{"code":404,"message":"Product \"product_not_viewable_by_redactor\" does not exist or you do not have permission to access it."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products/product_not_viewable_by_redactor');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_not_viewable_by_redactor');
    }

    public function testProductNotDeletableByUuidByUserWhoHasNoPermissions()
    {
        $product = $this->createProduct('second_product_not_viewable_by_redactor', [
            'categories' => ['categoryB']
        ]);

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"Product \"second_product_not_viewable_by_redactor\" does not exist or you do not have permission to access it."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products-uuid/' . $product->getUuid()->toString());

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('second_product_not_viewable_by_redactor');
    }

    /**
     * @param string $productIdentifier
     */
    private function assertProductDeleted(string $productIdentifier): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);

        $this->assertNull($product);
    }

    /**
     * @param string $productIdentifier
     */
    private function assertProductNotDeleted(string $productIdentifier): void
    {
        $product = $this->get('pim_catalog.repository.product_without_permission')->findOneByIdentifier($productIdentifier);

        $this->assertNotNull($product);
    }
}
