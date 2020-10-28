<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductProposalNormalizer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductProposalIndexerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        Client $productProposalClient
    ) {
        $this->beConstructedWith($normalizer, $productProposalClient, 'product_proposal');
    }

    function it_is_a_bulk_remover()
    {
        $this->shouldHaveType(BulkRemoverInterface::class);
    }

    function it_bulk_removes_product_proposals($productProposalClient)
    {
        $productProposalClient->bulkDelete([
            'product_draft_1',
            'product_draft_12',
            'product_draft_4'
        ])->shouldBeCalled();

        $this->removeAll([1, 12, 4], [])->shouldReturn(null);
    }

    function it_indexes_product_proposal(
        $normalizer,
        $productProposalClient,
        ProductModel $productModelDraft
    ) {
        $productDraftNormalized = [
            'id' => 1
        ];

        $normalizer->normalize($productModelDraft, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->willReturn($productDraftNormalized);

        $productProposalClient->index(1, $productDraftNormalized, Argument::type(Refresh::class))
            ->shouldBeCalled();

        $this->index($productModelDraft, [])->shouldReturn(null);
    }

    function it_removes_product_proposal($productProposalClient)
    {
        $productProposalClient->search(
            ['query' => ['term' => ['id' => 'product_draft_1']]]
        )->willReturn(['hits' => ['total' => ['value' => 1]]]);

        $productProposalClient->delete('product_draft_1')->shouldBeCalled();
        $productProposalClient->refreshIndex()->shouldNotBeCalled();

        $this->remove(1, [])->shouldReturn(null);
    }

    function it_removes_product_proposal_and_refresh_index($productProposalClient)
    {
        $productProposalClient->search(
            ['query' => ['term' => ['id' => 'product_draft_1']]]
        )->willReturn(['hits' => ['total' => ['value' => 1]]]);

        $productProposalClient->delete('product_draft_1')->shouldBeCalled();
        $productProposalClient->refreshIndex()->shouldBeCalled();

        $this->remove(1, ['index_refresh' => Refresh::enable()])->shouldReturn(null);
    }
}
