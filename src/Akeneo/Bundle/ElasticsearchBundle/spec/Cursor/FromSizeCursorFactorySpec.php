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

    function let(Client $searchEngine, ObjectManager $om)
    {
        $this->beConstructedWith(
            $searchEngine,
            $om,
            ProductInterface::class,
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

    function it_creates_a_cursor($om, $searchEngine, CursorableRepositoryInterface $cursorableRepository)
    {
        $om->getRepository(ProductInterface::class)->willReturn($cursorableRepository);
        $searchEngine->search('pim_catalog_product', ['size' => 100, 'sort' => ['_uid' => 'asc'], 'from' => 10])->willReturn([
            'hits' => [
                'total' => 0,
                'hits' => []
            ]
        ]);

        $this->createCursor([], ['page_size' => 100, 'limit' => 150, 'from' => 10])
            ->shouldBeAnInstanceOf(CursorInterface::class);
    }

    function it_throws_an_exception_if_repository_is_not_cursorable($om)
    {
        $om->getRepository(ProductInterface::class)->willReturn(Argument::any());

        $this->shouldThrow(
            InvalidObjectException::objectExpected(ProductInterface::class, CursorableRepositoryInterface::class)
        )->during('createCursor', [[], ['limit' => 150, 'from' => 0]]);
    }
}
