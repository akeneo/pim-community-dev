<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\CursorFactory;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class CursorFactorySpec extends ObjectBehavior
{
    function let(
        Client $searchEngine,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
    ) {
        $this->beConstructedWith(
            $searchEngine,
            $productRepository,
            $productModelRepository,
            100
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CursorFactory::class);
        $this->shouldImplement(CursorFactoryInterface::class);
    }

    function it_creates_a_cursor($searchEngine)
    {
        $searchEngine->search(
            ['size' => 100, 'sort' => ['id' => 'asc']]
        )->willReturn(
            [
                'hits' => [
                    'total' => 0,
                    'hits'  => []
                ]
            ]
        );

        $this->createCursor(['_source' => ['identifier']], ['page_size' => 100])
            ->shouldBeAnInstanceOf(CursorInterface::class);
    }
}
