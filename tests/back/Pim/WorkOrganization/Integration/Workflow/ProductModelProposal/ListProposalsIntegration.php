<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ProductModelProposal;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use PHPUnit\Framework\Assert;

class ListProposalsIntegration extends AbstractDraft
{
    /**
     * @test
     */
    public function it_gets_a_product_proposal_when_categories_are_on_own_level(): void
    {
        $this->createProductDraft('watch', 'collection', ['spring_2016']);
        $this->sendProductDraftForApproval('mary', 'watch');

        $admin = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $results = $this->get('pimee_workflow.repository.product_draft')->findApprovableByUser($admin);
        Assert::assertCount(1, $results);
        $productDraft = $results[0];
        Assert::assertInstanceOf(ProductDraft::class, $productDraft);
        Assert::assertEquals('watch', $productDraft->getEntityWithValue()->getIdentifier());
    }

    /**
     * @test
     */
    public function it_gets_a_product_proposal_when_categories_are_inherited(): void
    {
        $this->createProductDraft('1111111270', 'weight', ['unit' => 'GRAM', 'amount' => 500]);
        $this->sendProductDraftForApproval('mary', '1111111270');

        $admin = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $results = $this->get('pimee_workflow.repository.product_draft')->findApprovableByUser($admin);
        Assert::assertCount(1, $results);
        $productDraft = $results[0];
        Assert::assertInstanceOf(ProductDraft::class, $productDraft);
        Assert::assertEquals('1111111270', $productDraft->getEntityWithValue()->getIdentifier());
    }

    /**
     * @test
     */
    public function it_gets_a_product_model_proposal_when_categories_are_on_own_level(): void
    {
        $this->createProductModelDraft('plain', 'collection', ['spring_2016']);
        $this->sendProductModelDraftForApproval('mary', 'plain');

        $admin = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $results = $this->get('pimee_workflow.repository.product_model_draft')->findApprovableByUser($admin);
        Assert::assertCount(1, $results);
        $productModelDraft = $results[0];
        Assert::assertInstanceOf(ProductModelDraft::class, $productModelDraft);
        Assert::assertEquals('plain', $productModelDraft->getEntityWithValue()->getCode());
    }

    /**
     * @test
     */
    public function it_gets_a_product_model_proposal_when_categories_are_inherited(): void
    {
        $this->createProductModelDraft('plain_red', 'composition', 'cotton');
        $this->sendProductModelDraftForApproval('mary', 'plain_red');

        $admin = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $results = $this->get('pimee_workflow.repository.product_model_draft')->findApprovableByUser($admin);
        Assert::assertCount(1, $results);
        $productModelDraft = $results[0];
        Assert::assertInstanceOf(ProductModelDraft::class, $productModelDraft);
        Assert::assertEquals('plain_red', $productModelDraft->getEntityWithValue()->getCode());
    }
}
