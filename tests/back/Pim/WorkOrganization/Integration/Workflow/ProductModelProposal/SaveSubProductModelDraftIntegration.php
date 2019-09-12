<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ProductModelProposal;

use PHPUnit\Framework\Assert;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;

class SaveSubProductModelDraftIntegration extends AbstractDraft
{
    public function testSuccessfullyToSaveASubProductModelDraft()
    {
        $this->createProductModelDraft('jack_brown', 'composition', 'cotton');
        $productModelDrafts = $this->get('pimee_workflow.repository.product_model_draft')->findAll();

        Assert::assertCount(1, $productModelDrafts);
        Assert::assertSame(
            ['composition' => ['<all_channels>' => ['<all_locales>' => 'cotton']]],
            current($productModelDrafts)->getRawValues()
        );
        Assert::assertSame(0, current($productModelDrafts)->getStatus());
        Assert::assertCount(0, $this->get('pimee_workflow.repository.product_draft')->findAll());
    }

    public function testSuccessfullyToFetchProposalFromES()
    {
        $subProductModel = $this->createProductModelDraft('jack_brown', 'composition', 'cotton');
        $productModelDraft = $this->get('pimee_workflow.repository.product_model_draft')
            ->findUserEntityWithValuesDraft($subProductModel, 'mary');
        $productModelDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);

        $this->get('pimee_workflow.saver.product_model_draft')->save($productModelDraft);

        $esClient = $this->get('akeneo_elasticsearch.client.product_proposal');
        $esClient->refreshIndex();

        $allDocuments = $esClient->search([
            'query' => [
                'match_all' => new \StdClass(),
            ],
        ]);

        $draft = $allDocuments['hits']['hits'][0]['_source'];

        Assert::assertSame(1, $allDocuments['hits']['total']['value']);
        Assert::assertSame(['composition-textarea' => ['<all_channels>' => ['<all_locales>' => 'cotton']]], $draft['values']);
        Assert::assertSame(['tshirts'], $draft['categories']);
        Assert::assertSame('Mary', $draft['author']);
        Assert::assertSame(ProductModelDraft::class, $draft['document_type']);
    }
}
