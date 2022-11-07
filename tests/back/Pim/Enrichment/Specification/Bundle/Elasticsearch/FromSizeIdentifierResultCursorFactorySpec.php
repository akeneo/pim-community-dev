<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

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
        $uuid = Uuid::uuid4();

        $esQuery = [
            'sort'  => [],
            'query' => [],
            '_source' => ['identifier']
        ];

        $options = [
            'limit' => 25,
            'from'  => 0,
        ];

        $result = ['hits' => [
            'total' => ['value' => 42, 'relation' => 'eq'],
            'hits'  => [
                ['_source' => ['identifier' => 'product_1', 'document_type' => ProductInterface::class, 'id' => 'product_' . $uuid->toString()]],
                ['_source' => ['identifier' => 'product_model_2', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_product_model_2']],
            ]
        ]];

        $esClient->search(
            [
                'sort' => ['id' => 'asc'],
                'query' => [],
                '_source' => ['identifier', 'document_type', 'id'],
                "track_total_hits" => true,
                'size' => 25,
                'from' => 0
            ]
        )->willReturn($result);

        $this->createCursor($esQuery, $options)->shouldBeLike(new IdentifierResultCursor(
            [
                new IdentifierResult('product_1', ProductInterface::class, 'product_' . $uuid->toString()),
                new IdentifierResult('product_model_2', ProductModelInterface::class, 'product_model_product_model_2'),
            ],
            42,
            new ElasticsearchResult($result)
        ));
    }
}
