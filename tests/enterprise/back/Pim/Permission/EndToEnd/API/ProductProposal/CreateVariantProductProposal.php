<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductProposal;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
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
class CreateVariantProductProposalIntegration extends AbstractProposal
{
    public function testCreateProductProposalSuccessful()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'variant_product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/variant_product_with_draft/proposal', [], [], [], '{}');

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft($productDraft->getEntityWithValue(), 'mary');
        $this->assertSame(ProductDraft::READY, $productDraft->getStatus());
        $this->assertEquals([
            'a_localized_and_scopable_text_area' => [
                ['data' => 'Modified US in draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ]
        ], $productDraft->getChanges()['values']);
    }

    public function testUserIsOwner()
    {
        $this->createDefaultProductDraft('mary', 'variant_product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('POST', 'api/rest/v1/products/variant_product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"variant_product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testVariantProductHasNoDraft()
    {
        $this->createDefaultProductDraft('kevin', 'variant_product_modified_by_kevin');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/variant_product_modified_by_kevin/proposal', [], [], [], '{}');

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
        $productDraft = $this->createDefaultProductDraft('mary', 'variant_product_with_draft');
        $this->get('pimee_workflow.manager.product_draft')->markAsReady($productDraft);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/variant_product_with_draft/proposal', [], [], [], '{}');

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
        $this->loginAs('mary');
        $productDraft = $this->createDefaultProductDraft('mary', 'variant_product_with_draft');

        $this->loginAs('admin');
        $this->updateProduct($productDraft->getEntityWithValue(), [
            new SetCategories(['categoryA1']),
        ]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/variant_product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You only have view permission on the product \\"variant_product_with_draft\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserWithoutAnyPermission()
    {
        $this->loginAs('mary');
        $productDraft = $this->createDefaultProductDraft('mary', 'variant_product_with_draft');

        $this->loginAs('admin');
        $this->updateProduct($productDraft->getEntityWithValue(), [
            new SetCategories(['categoryB']),
        ]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/variant_product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":404,"message":"Product \\"variant_product_with_draft\\" does not exist."}
JSON;

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testCreateProposalOnAnUnclassifiedVariantProduct()
    {
        $this->loginAs('mary');
        $productDraft = $this->createDefaultProductDraft('mary', 'variant_product_with_draft');

        $this->loginAs('admin');
        $this->updateProduct($productDraft->getEntityWithValue(), [new SetCategories([])]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('POST', 'api/rest/v1/products/variant_product_with_draft/proposal', [], [], [], '{}');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"variant_product_with_draft\\", you cannot send a draft for approval."}
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
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'code' => 'product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_multi_select' => [
                    ['data' => ['optionA', 'optionB'], 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $product = $this->createVariantProduct($productIdentifier, [
            'categories' => ['categoryA'],
            'parent' => 'product_model',
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
