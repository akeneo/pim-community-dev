<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Normalizer\Indexing\ProductModelProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProposalIndexerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        Client $productModelProposalClient
    ) {
        $this->beConstructedWith($normalizer, $productModelProposalClient, 'pimee_workflow_product_proposal');
    }

    function it_indexes_product_model_proposal(
        $normalizer,
        $productModelProposalClient,
        ProductModel $productModelDraft
    ) {
        $productModelDraftNormalized = [
            'id' => 1
        ];

        $normalizer->normalize($productModelDraft, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)
            ->willReturn($productModelDraftNormalized);

        $productModelProposalClient->index('pimee_workflow_product_proposal', 1, $productModelDraftNormalized)->shouldBeCalled();

        $this->index($productModelDraft, [])->shouldReturn(null);
    }

    function it_indexes_all_product_model_proposals(
        $normalizer,
        $productModelProposalClient,
        EntityWithValuesDraftInterface $productModelDraft1,
        EntityWithValuesDraftInterface $productModelDraft2
    ) {
        $productModelDraftNormalized1 = ['id' => 1];
        $productModelDraftNormalized2 = ['id' => 2];

        $normalizer->normalize($productModelDraft1, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)
            ->willReturn($productModelDraftNormalized1);

        $normalizer->normalize($productModelDraft2, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)
            ->willReturn($productModelDraftNormalized2);

        $productModelProposalClient->bulkIndexes(
            'pimee_workflow_product_proposal',
            [$productModelDraftNormalized1, $productModelDraftNormalized2],
            'id',
            Refresh::waitFor()
        )->shouldBeCalled();

        $this->indexAll([$productModelDraft1, $productModelDraft2], [])->shouldReturn(null);
    }

    function it_indexes_all_product_model_proposals_with_option_on_refresh(
        $normalizer,
        $productModelProposalClient,
        EntityWithValuesDraftInterface $productModelDraft1,
        EntityWithValuesDraftInterface $productModelDraft2
    ) {
        $productModelDraftNormalized1 = ['id' => 1];
        $productModelDraftNormalized2 = ['id' => 2];

        $normalizer->normalize($productModelDraft1, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)
            ->willReturn($productModelDraftNormalized1);

        $normalizer->normalize($productModelDraft2, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)
            ->willReturn($productModelDraftNormalized2);

        $productModelProposalClient->bulkIndexes(
            'pimee_workflow_product_proposal',
            [$productModelDraftNormalized1, $productModelDraftNormalized2],
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $this->indexAll([$productModelDraft1, $productModelDraft2], ['index_refresh' => Refresh::disable()])->shouldReturn(null);
    }

    function it_throws_an_exception_if_id_does_not_exist_on_index($normalizer, EntityWithValuesDraftInterface $productModelDraft)
    {
        $normalizer->normalize($productModelDraft, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)
            ->willReturn([]);

        $this->shouldThrow(
            \InvalidArgumentException::class
        )->during('index', [$productModelDraft, []]);
    }

    function it_throws_an_exception_if_id_does_not_exist_on_bulk_index($normalizer, EntityWithValuesDraftInterface $productModelDraft)
    {
        $normalizer->normalize($productModelDraft, ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX)
            ->willReturn([]);

        $this->shouldThrow(
            \InvalidArgumentException::class
        )->during('indexAll', [[$productModelDraft], []]);
    }

    function it_removes_product_model_proposal($productModelProposalClient)
    {
        $productModelProposalClient->search(
            'pimee_workflow_product_proposal',
            ['query' => ['term' => ['id' => 'product_model_draft_1']]]
        )->willReturn(['hits' => ['total' => 1]]);

        $productModelProposalClient->delete('pimee_workflow_product_proposal', 'product_model_draft_1')->shouldBeCalled();

        $this->remove(1, [])->shouldReturn(null);
    }

    function it_does_not_remove_product_model_proposal_if_it_does_not_exist($productModelProposalClient)
    {
        $productModelProposalClient->search(
            'pimee_workflow_product_proposal',
            ['query' => ['term' => ['id' => 'product_model_draft_1']]]
        )->willReturn(['hits' => ['total' => 0]]);

        $productModelProposalClient->delete('pimee_workflow_product_proposal', 'product_model_draft_1')->shouldNotBeCalled();

        $this->remove(1, [])->shouldReturn(null);
    }

    function it_removes_all_product_model_proposals($productModelProposalClient)
    {
        $productModelProposalClient->bulkDelete('pimee_workflow_product_proposal', [
            'product_model_draft_1',
            'product_model_draft_100',
            'product_model_draft_2'
        ])->shouldBeCalled();

        $this->removeAll([1, 100, 2], [])->shouldReturn(null);
    }
}
