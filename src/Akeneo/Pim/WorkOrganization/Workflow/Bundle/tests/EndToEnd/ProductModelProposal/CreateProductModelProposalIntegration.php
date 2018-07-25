<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\tests\EndToEnd\ProductModelProposal;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Symfony\Component\HttpFoundation\Response;

class CreateProductModelProposalIntegration extends ApiTestCase
{
    public function testCreateProductModelProposal()
    {
        $productModelDraft = $this->createProductModelDraft('mary', 'jack');

        $client = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $client->request('POST', 'api/rest/v1/product-models/jack/proposal', [], [], [], '{}');

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $productModelDraft = $this->get('pimee_workflow.repository.product_model_draft')->findUserEntityWithValuesDraft($productModelDraft->getEntityWithValue(), 'mary');
        $this->assertSame(ProductModelDraft::READY, $productModelDraft->getStatus());
    }

    public function testProductModelNotFound()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $client->request('POST', 'api/rest/v1/product-models/unknown/proposal', [], [], [], '{}');

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"Product model \\"unknown\\" does not exist."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserIsOwner()
    {
        $this->createProductModelDraft('mary', 'jack');

        $client = $this->createAuthenticatedClient([], [], null, null, 'Julia', 'Julia');
        $client->request('POST', 'api/rest/v1/product-models/jack/proposal', [], [], [], '{}');

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You have ownership on the product model \\"jack\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testProductModelHasNoDraft()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $client->request('POST', 'api/rest/v1/product-models/jack/proposal', [], [], [], '{}');

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
        $productModelDraft = $this->createProductModelDraft('mary', 'jack');
        $this->get('pimee_workflow.manager.product_model_draft')->markAsReady($productModelDraft);

        $client = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $client->request('POST', 'api/rest/v1/product-models/jack/proposal', [], [], [], '{}');

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
        $productModelDraft = $this->createProductModelDraft('mary', 'jack');

        $this->get('pim_catalog.updater.product_model')->update($productModelDraft->getEntityWithValue(), [
            'categories' => ['print_shoes'],
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productModelDraft->getEntityWithValue());

        $client = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $client->request('POST', 'api/rest/v1/product-models/jack/proposal', [], [], [], '{}');

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You only have view permission on the product model \\"jack\\", you cannot send a draft for approval."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function createProductModelDraft(string $userName, string $identifier): EntityWithValuesDraftInterface
    {
        $productModel = $this->get('pim_catalog.repository.product_model_without_permission')->findOneByIdentifier($identifier);

        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'values' => [
                'wash_temperature' => [
                    ['data' => 'hand', 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $productModelDraft = $this->get('pimee_workflow.product_model.builder.draft')->build($productModel, $userName);

        $this->get('pimee_workflow.saver.product_model_draft')->save($productModelDraft);

        return $productModelDraft;
    }
}
