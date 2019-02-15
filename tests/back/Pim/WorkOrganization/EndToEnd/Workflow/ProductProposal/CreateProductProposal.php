<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\roductProposal;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductProposal\AbstractProposal;
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
class CreateProductProposalIntegration extends AbstractProposal
{
    public function testCreateProductProposalSuccessful()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft($productDraft->getEntityWithValue(), 'mary');
        $this->assertSame(ProductDraft::READY, $productDraft->getStatus());
    }

    public function testProductNotFound()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/unknown_product/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":404,"message":"Product \\"unknown_product\\" does not exist."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testInvalidRequestBody()
    {
        $this->createDefaultProductDraft('mary', 'product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '');

        $expectedResponseContent =
            <<<JSON
{"code":400,"message":"Invalid json message received."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserIsOwner()
    {
        $this->createDefaultProductDraft('mary', 'product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testProductHasNoDraft()
    {
        $this->createDefaultProductDraft('kevin', 'product_modified_by_kevin');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_modified_by_kevin/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":422,"message":"You should create a draft before submitting it for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testApprovalAlreadySubmitted()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');
        $this->get('pimee_workflow.manager.product_draft')->markAsReady($productDraft);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":422,"message":"You already submit your draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserHasOnlyViewPermission()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->updateProduct($productDraft->getEntityWithValue(), [
            'categories' => ['categoryA1'],
        ]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You only have view permission on the product \\"product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserWithoutAnyPermission()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->updateProduct($productDraft->getEntityWithValue(), [
            'categories' => ['categoryB'],
        ]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":404,"message":"Product \\"product_with_draft\\" does not exist."}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testCreateProposalOnAnUnclassifiedProduct()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->updateProduct($productDraft->getEntityWithValue(), ['categories' => []]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    /**
     * @param string $userName
     * @param string $productIdentifier
     *
     * @return EntityWithValuesDraftInterface
     */
    private function createDefaultProductDraft(string $userName, string $productIdentifier): EntityWithValuesDraftInterface
    {
        $product = $this->createProduct($productIdentifier, [
            'categories' => ['categoryA'],
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Unchanged US', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ],
            ]
        ]);

        return $this->createEntityWithValuesDraft($userName, $product, [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Modified US in draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ]
        ]);
    }
}
