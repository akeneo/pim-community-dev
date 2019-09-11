<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\SearchAfterSizeCursor;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\SearchAfterSizeCursorFactory;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;

class SearchAfterSizeCursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let(Client $searchEngine, CursorableRepositoryInterface $cursorableRepository)
    {
        $this->beConstructedWith(
            $searchEngine,
            $cursorableRepository,
            SearchAfterSizeCursor::class,
            self::DEFAULT_BATCH_SIZE
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchAfterSizeCursorFactory::class);
        $this->shouldImplement(CursorFactoryInterface::class);
    }

    function it_creates_a_cursor($searchEngine)
    {
        $searchEngine->search(['size' => 100, 'sort' => ['_id' => 'asc'], 'search_after' => ['foo']])->willReturn([
            'hits' => [
                'total' => 0,
                'hits' => []
            ]
        ]);

        $this->createCursor([], ['page_size' => 100, 'limit' => 150, 'search_after' => ['foo']])
            ->shouldBeAnInstanceOf(CursorInterface::class);
    }

    function it_creates_a_cursor_with_search_after_identifier($searchEngine)
    {
        $searchEngine->search([
            'size' => 100, 'sort' => ['_id' => 'asc'], 'search_after' => ['2017-12-12', 'pim_catalog_product#foo']
        ])->willReturn([
            'hits' => [
                'total' => 0,
                'hits' => []
            ]
        ]);

        $this->createCursor([], ['page_size' => 100, 'limit' => 150, 'search_after' => ['2017-12-12'], 'search_after_unique_key' => 'foo'])
            ->shouldBeAnInstanceOf(CursorInterface::class);
    }
}
