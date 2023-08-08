<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\ProductProposal;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
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
class CreateProductProposalEndToEnd extends AbstractProposal
{
    public function testCreateProductProposalSuccessful()
    {
        $this->loginAs('mary');
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
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testInvalidRequestBody()
    {
        $this->loginAs('mary');
        $this->createDefaultProductDraft('mary', 'product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '');

        $expectedResponseContent =
            <<<JSON
{"code":400,"message":"Invalid json message received."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserIsOwner()
    {
        $this->loginAs('mary');
        $this->createDefaultProductDraft('mary', 'product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testProductHasNoDraft()
    {
        $this->loginAs('kevin');
        $this->createDefaultProductDraft('kevin', 'product_modified_by_kevin');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_modified_by_kevin/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":422,"message":"You should create a draft before submitting it for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testApprovalAlreadySubmitted()
    {
        $this->loginAs('mary');
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
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserHasOnlyViewPermission()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $this->loginAs('mary');
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->loginAs('admin');
        $this->updateProduct(
            $productDraft->getEntityWithValue(), [
            new SetCategories(['categoryA1']),
        ], 'admin');

        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You only have view permission on the product \\"product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserWithoutAnyPermission()
    {
        $this->loginAs('mary');
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->loginAs('admin');
        $this->updateProduct($productDraft->getEntityWithValue(), [
            new SetCategories(['categoryB']),
        ]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":404,"message":"Product \\"product_with_draft\\" does not exist or you do not have permission to access it."}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testCreateProposalOnAnUnclassifiedProduct()
    {
        $this->loginAs('mary');
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->loginAs('admin');
        $this->updateProduct($productDraft->getEntityWithValue(), [new SetCategories([])]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testAccessDeniedWhenCreateProductProposalWithoutTheAcl()
    {
        $this->loginAs('mary');
        $this->createDefaultProductDraft('mary', 'product_with_draft');
        $this->removeAclFromRole('action:pim_api_product_edit', 'ROLE_USER');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/product_with_draft/proposal', [], [], [], '{}');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @param string $userName
     * @param string $productIdentifier
     *
     * @return EntityWithValuesDraftInterface
     */
    private function createDefaultProductDraft(string $userName, string $productIdentifier): EntityWithValuesDraftInterface
    {
        $product = $this->createProduct(
            $productIdentifier, [
                new SetCategories(['categoryA']),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'Unchanged US'),
            ],
            $userName
        );

        return $this->createEntityWithValuesDraft($userName, $product, [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Modified US in draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ]
        ]);
    }
}
