<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\FromSizeCursor;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\FromSizeCursorFactory;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;

class FromSizeCursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let(Client $searchEngine, CursorableRepositoryInterface $cursorableRepository)
    {
        $this->beConstructedWith(
            $searchEngine,
            $cursorableRepository,
                FromSizeCursor::class,
            self::DEFAULT_BATCH_SIZE
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FromSizeCursorFactory::class);
        $this->shouldImplement(CursorFactoryInterface::class);
    }

    function it_creates_a_cursor($searchEngine)
    {
        $searchEngine->search(['size' => 100, 'sort' => ['_id' => 'asc'], 'from' => 10])->willReturn([
            'hits' => [
                'total' => 0,
                'hits' => []
            ]
        ]);

        $this->createCursor([], ['page_size' => 100, 'limit' => 150, 'from' => 10])
            ->shouldBeAnInstanceOf(CursorInterface::class);
    }
}
