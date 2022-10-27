<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\ProductProposal;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final class CreateProductProposalWithUuidEndToEnd extends ApiTestCase
{
    private string $productWithDraftUuid = '97086c99-2b77-42ae-83fc-8ffb3891febf';
    private string $productWithoutDraftUuid = '7aa14202-1af3-4b8f-b5df-1855c8c3df01';

    public function testCreateProductProposalSuccessful(): void
    {
        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft(
            $this->get('pim_catalog.repository.product')->find($this->productWithDraftUuid),
            'mary'
        );
        $this->assertSame(ProductDraft::READY, $productDraft->getStatus());
    }

    public function testProductNotFound(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $response = $this->getResponse($uuid, 'mary');

        $expectedResponseContent =
            <<<JSON
{"code":404,"message":"Product \\"{$uuid}\\" does not exist."}
JSON;

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserIsOwner(): void
    {
        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"{$this->productWithDraftUuid}\\", you cannot send a draft for approval."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'julia');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testItMatchesUuidWithUppercase(): void
    {
        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You have ownership on the product \\"{$this->productWithDraftUuid}\\", you cannot send a draft for approval."}
JSON;

        $response = $this->getResponse(\strtoupper($this->productWithDraftUuid), 'julia');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testProductHasNoDraft(): void
    {
        $expectedResponseContent =
            <<<JSON
{"code":422,"message":"You should create a draft before submitting it for approval."}
JSON;

        $response = $this->getResponse($this->productWithoutDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testApprovalAlreadySubmitted(): void
    {
        $drafts = $this->get('pimee_workflow.repository.product_draft')->findAll();
        foreach ($drafts as $draft) {
            $this->get('pimee_workflow.manager.product_draft')->markAsReady($draft);
        }

        $expectedResponseContent =
            <<<JSON
{"code":422,"message":"You already submitted your draft for approval."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserHasOnlyViewPermission(): void
    {
        $this->upsertProduct($this->productWithDraftUuid, 'julia', [
            new SetCategories(['categoryA1']),
        ]);

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You only have view permission on the product \\"{$this->productWithDraftUuid}\\", you cannot send a draft for approval."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserWithoutAnyPermission(): void
    {
        $this->upsertProduct($this->productWithDraftUuid, 'julia', [
            new SetCategories(['categoryB']),
        ]);

        $expectedResponseContent =
            <<<JSON
{"code":404,"message":"Product \\"{$this->productWithDraftUuid}\\" does not exist or you do not have permission to access it."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testAccessDeniedWhenCreateProductProposalWithoutTheAcl(): void
    {
        $this->removeAclFromRole('action:pim_api_product_edit', 'ROLE_USER');

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        // create 3 products
        $productWithDraft = $this->upsertProduct($this->productWithDraftUuid, 'julia', [
            new SetIdentifierValue('sku', 'product_with_draft'),
            new AddCategories(['categoryA']),
            new SetFamily('familyA'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'Original text US'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'Texte original FR'),
        ]);
        $this->upsertProduct($this->productWithoutDraftUuid, 'julia', [
            new SetIdentifierValue('sku', 'product_without_draft'),
            new AddCategories(['categoryA']),
        ]);

        // create a draft for mary
        $valueEn = $productWithDraft->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce');
        $productWithDraft->removeValue($valueEn)->addValue(
            ScalarValue::scopableLocalizableValue(
                'a_localized_and_scopable_text_area',
                'The updated US text',
                'ecommerce',
                'en_US',
            )
        );
        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build(
            $productWithDraft,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($this->user('mary'))
        );
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);
    }

    /**
     * {inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['permission', 'proposal']);
    }

    private function upsertProduct(string $uuid, string $username, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username);
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->user($username)->getId(),
                ProductUuid::fromUuid(Uuid::fromString($uuid)),
                $userIntents
            )
        );
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        return $this->get('pim_catalog.repository.product')->find($uuid);
    }

    private function user(string $username): UserInterface
    {
        return $this->get('pim_user.repository.user')->findOneByIdentifier($username);
    }

    private function getResponse(string $uuid, string $username): Response
    {
        $client = $this->createAuthenticatedClient([], [], null, null, $username, $username);
        $client->request('POST', "api/rest/v1/products-uuid/{$uuid}/proposal");

        return $client->getResponse();
    }
}
