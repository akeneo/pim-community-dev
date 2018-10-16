<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\FromSizeCursorFactory;

class FromSizeCursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let(
        Client $searchEngine,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith(
            $searchEngine,
            $productRepository,
            $productModelRepository,
            self::DEFAULT_BATCH_SIZE,
            'pim_catalog_product'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FromSizeCursorFactory::class);
        $this->shouldImplement(CursorFactoryInterface::class);
    }

    function it_creates_a_cursor($searchEngine)
    {
        $searchEngine->search(
            'pim_catalog_product',
            ['size' => 100, 'sort' => ['_uid' => 'asc'], 'from' => 10]
        )->willReturn(
            [
                'hits' => [
                    'total' => 0,
                    'hits'  => []
                ]
            ]
        );

        $this->createCursor(['_source' => ['identifier']], ['page_size' => 100, 'limit' => 150, 'from' => 10])
            ->shouldBeAnInstanceOf(CursorInterface::class);
    }
}
