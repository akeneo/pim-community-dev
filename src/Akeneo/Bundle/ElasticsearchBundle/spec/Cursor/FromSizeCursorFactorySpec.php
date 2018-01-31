<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Cursor\FromSizeCursor;
use Akeneo\Bundle\ElasticsearchBundle\Cursor\FromSizeCursorFactory;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
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
        $searchEngine->search('pim_catalog_product', ['size' => 100, 'sort' => ['_uid' => 'asc'], 'from' => 10])->willReturn([
            'hits' => [
                'total' => 0,
                'hits' => []
            ]
        ]);

        $this->createCursor([], ['page_size' => 100, 'limit' => 150, 'from' => 10])
            ->shouldBeAnInstanceOf(CursorInterface::class);
    }
}
