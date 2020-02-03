<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PhpSpec\ObjectBehavior;

class FromSizeIdentifierResultCursorFactorySpec extends ObjectBehavior
{
    function let(Client $esClient)
    {
        $this->beConstructedWith($esClient);
    }

    function it_is_a_cursor_factory()
    {
        $this->shouldImplement(CursorFactoryInterface::class);
    }

    function it_creates_a_product_identifier_cursor($esClient)
    {
        $esQuery = [
            'sort'  => [],
            'query' => [],
            '_source' => ['identifier']
        ];

        $options = [
            'limit' => 25,
            'from'  => 0,
        ];

        $esClient->search(
            [
                'sort' => ['_id' => 'asc'],
                'query' => [],
                '_source' => ['identifier', 'document_type'],
                "track_total_hits" => true,
                'size' => 25,
                'from' => 0
            ]
        )->willReturn(['hits' => [
            'total' => ['value' => 42, 'relation' => 'eq'],
            'hits'  => [
                ['_source' => ['identifier' => 'product_1', 'document_type' => ProductInterface::class]],
                ['_source' => ['identifier' => 'product_model_2', 'document_type' => ProductModelInterface::class]]
            ]
        ]]);

        $this->createCursor($esQuery, $options)->shouldBeLike(new IdentifierResultCursor(
            [
                new IdentifierResult('product_1', ProductInterface::class),
                new IdentifierResult('product_model_2', ProductModelInterface::class),
            ], 42
        ));
    }
}
