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

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductProposalIndexerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        Client $esClient
    ) {
        $this->beConstructedWith($normalizer, $esClient, 'product_proposal');
    }

    function it_is_a_bulk_remover()
    {
        $this->shouldHaveType(BulkRemoverInterface::class);
    }

    function it_bulk_removes_product_proposals($esClient)
    {
        $esClient->bulkDelete('product_proposal', [
            'product_draft_1',
            'product_draft_12',
            'product_draft_4'
        ])->shouldBeCalled();

        $this->removeAll([1, 12, 4], [])->shouldReturn(null);
    }
}
