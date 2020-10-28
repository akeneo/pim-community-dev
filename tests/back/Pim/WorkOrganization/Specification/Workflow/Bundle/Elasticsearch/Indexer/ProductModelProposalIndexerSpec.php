<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductModelProposalNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProposalIndexerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        Client $productModelProposalClient
    ) {
        $this->beConstructedWith($normalizer, $productModelProposalClient);
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

        $productModelProposalClient->index(1, $productModelDraftNormalized, Argument::type(Refresh::class))->shouldBeCalled();

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
            ['query' => ['term' => ['id' => 'product_model_draft_1']]]
        )->willReturn(['hits' => ['total' => ['value' => 1]]]);

        $productModelProposalClient->delete('product_model_draft_1')->shouldBeCalled();
        $productModelProposalClient->refreshIndex()->shouldNotBeCalled();

        $this->remove(1, [])->shouldReturn(null);
    }

    function it_removes_product_model_proposal_and_refresh_index($productModelProposalClient)
    {
        $productModelProposalClient->search(
            ['query' => ['term' => ['id' => 'product_model_draft_1']]]
        )->willReturn(['hits' => ['total' => ['value' => 1]]]);

        $productModelProposalClient->delete('product_model_draft_1')->shouldBeCalled();
        $productModelProposalClient->refreshIndex()->shouldBeCalled();

        $this->remove(1, ['index_refresh' => Refresh::enable()])->shouldReturn(null);
    }

    function it_does_not_remove_product_model_proposal_if_it_does_not_exist($productModelProposalClient)
    {
        $productModelProposalClient->search(
            ['query' => ['term' => ['id' => 'product_model_draft_1']]]
        )->willReturn(['hits' => ['total' => ['value' => 0]]]);

        $productModelProposalClient->delete('product_model_draft_1')->shouldNotBeCalled();

        $this->remove(1, [])->shouldReturn(null);
    }

    function it_removes_all_product_model_proposals($productModelProposalClient)
    {
        $productModelProposalClient->bulkDelete([
            'product_model_draft_1',
            'product_model_draft_100',
            'product_model_draft_2'
        ])->shouldBeCalled();

        $this->removeAll([1, 100, 2], [])->shouldReturn(null);
    }
}
