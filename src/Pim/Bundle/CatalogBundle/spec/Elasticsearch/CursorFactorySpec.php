<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\CursorFactory;

class CursorFactorySpec extends ObjectBehavior
{
    function let(
        Client $searchEngine,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith(
            $searchEngine,
            $productRepository,
            $productModelRepository,
            100,
            'pim_catalog_product'
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
            'pim_catalog_product',
            ['size' => 100, 'sort' => ['_uid' => 'asc']]
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
