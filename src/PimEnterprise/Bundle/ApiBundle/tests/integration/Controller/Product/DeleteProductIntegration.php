<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

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
class DeleteProductIntegration extends AbstractProductTestCase
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

    public function testProductNotDeletableByUserWhoHasNoPermissions()
    {
        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You can neither view, nor update, nor delete the product \"product_not_viewable_by_redactor\", as it is only categorized in categories on which you do not have a view permission."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('DELETE', 'api/rest/v1/products/product_not_viewable_by_redactor');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_not_viewable_by_redactor');
    }

    public function testDeleteAPublishedProductFails()
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');
        $this->get('pimee_workflow.manager.published_product')->publish($product);

        $expectedResponseContent =
<<<JSON
{"code":422,"message":"Impossible to remove a published product"}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('DELETE', 'api/rest/v1/products/product_viewable_by_everybody_1');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
        $this->assertProductNotDeleted('product_viewable_by_everybody_1');
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
