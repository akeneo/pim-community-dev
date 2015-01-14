<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Cursor;

use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface;

class PaginatorSpec extends ObjectBehavior
{
    const PAGE_SIZE = 10;

    public function let(CursorInterface $cursor)
    {
        $this->beConstructedWith($cursor, self::PAGE_SIZE);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\Paginator');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorInterface');
    }

    public function it_iterate_by_page_over_cursor(CursorInterface $cursor)
    {
        $page1 = [new Entity(1), new Entity(2), new Entity(3), new Entity(4), new Entity(5), new Entity(6),
            new Entity(7), new Entity(8), new Entity(9), new Entity(10)];
        $page2 = [new Entity(11), new Entity(12), new Entity(13)];
        $entities = array_merge($page1, $page2);

        $cursor->count()->shouldBeCalled()->willReturn(13);
        $cursor->next()->shouldBeCalled()->will(function () use ($cursor, &$entities) {
            $cursor->current()->willReturn(array_shift($entities));
        });
        $cursor->rewind()->shouldBeCalled()->willReturn(null);

        $this->rewind()->shouldReturn(null);
        $this->getCurrentPage()->shouldReturn(0);
        $this->hasNextPage()->shouldReturn(true);
        $this->getNextPage()->shouldReturn($page1);
        $this->getCurrentPage()->shouldReturn(1);
        $this->hasNextPage()->shouldReturn(true);
        $this->getNextPage()->shouldReturn($page2);
        $this->getCurrentPage()->shouldReturn(2);
        $this->hasNextPage()->shouldReturn(false);
        $this->getNextPage()->shouldReturn([]);
    }

    public function it_is_rewindable(CursorInterface $cursor)
    {
        $page1 = [new Entity(1), new Entity(2), new Entity(3), new Entity(4), new Entity(5), new Entity(6),
            new Entity(7), new Entity(8), new Entity(9), new Entity(10)];
        $page2 = [new Entity(11), new Entity(12), new Entity(13)];
        $entities = array_merge($page1, $page2);

        $cursor->count()->shouldBeCalled()->willReturn(13);
        $cursor->next()->shouldBeCalled()->will(function () use ($cursor, &$entities) {
            $cursor->current()->willReturn(array_shift($entities));
        });
        $cursor->rewind()->shouldBeCalled()->will(function () use ($cursor, &$entities, $page1, $page2) {
            $entities = array_merge($page1, $page2);
        });

        $this->rewind()->shouldReturn(null);
        $this->getCurrentPage()->shouldReturn(0);
        $this->hasNextPage()->shouldReturn(true);
        $this->getNextPage()->shouldReturn($page1);
        $this->getCurrentPage()->shouldReturn(1);

        $this->rewind()->shouldReturn(null);
        $this->getCurrentPage()->shouldReturn(0);
        $this->hasNextPage()->shouldReturn(true);
        $this->getNextPage()->shouldReturn($page1);
        $this->getCurrentPage()->shouldReturn(1);
        $this->hasNextPage()->shouldReturn(true);
        $this->getNextPage()->shouldReturn($page2);
        $this->getCurrentPage()->shouldReturn(2);
        $this->hasNextPage()->shouldReturn(false);
        $this->getNextPage()->shouldReturn([]);
    }
}

class Entity {

    protected  $id;
    public function __construct($id)
    {
        $this->id=$id;
    }

}
