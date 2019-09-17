<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\Cursor;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\CursorFactory;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;

class CursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let(Client $searchEngine, CursorableRepositoryInterface $cursorableRepository)
    {
        $this->beConstructedWith(
            $searchEngine,
            $cursorableRepository,
            Cursor::class,
            self::DEFAULT_BATCH_SIZE,
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
        $searchEngine->search(['size' => 100, 'sort' => ['_id' => 'asc']])->willReturn([
            'hits' => [
                'total' => 0,
                'hits' => []
            ]
        ]);

        $this->createCursor([])->shouldBeAnInstanceOf(CursorInterface::class);
    }
}
