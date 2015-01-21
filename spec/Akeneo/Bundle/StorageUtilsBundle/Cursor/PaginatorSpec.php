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

    public function it_is_a_paginator()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\Paginator');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorInterface');
    }

    public function it_iterate_by_page_over_cursor(CursorInterface $cursor)
    {
        $page1 = [
            new Entity(1),
            new Entity(2),
            new Entity(3),
            new Entity(4),
            new Entity(5),
            new Entity(6),
            new Entity(7),
            new Entity(8),
            new Entity(9),
            new Entity(10)
        ];
        $page2 = [new Entity(11), new Entity(12), new Entity(13)];
        $data = array_merge($page1, $page2);

        $cursor->count()->shouldBeCalled()->willReturn(13);
        $cursor->next()->shouldBeCalled()->will(function () use ($cursor, &$data) {
            $item = array_shift($data);
            if ($item === null) {
                $item = false;
            }
            $cursor->current()->willReturn($item);
        });
        $cursor->rewind()->shouldBeCalled()->will(function () use ($cursor, &$data, $page1, $page2) {
            $data = array_merge($page1, $page2);
            $item = array_shift($data);
            if ($item === null) {
                $item = false;
            }
            $cursor->current()->willReturn($item);
        });

        // for each call sequence
        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($page1);
        $this->key()->shouldReturn(0);

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($page2);
        $this->key()->shouldReturn(1);

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(false);

        // check behaviour after the end of data
        $this->current()->shouldReturn(false);
        $this->key()->shouldReturn(null);

        // methods that not iterate can be called twice
        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($page1);
        $this->current()->shouldReturn($page1);
        $this->key()->shouldReturn(0);
        $this->key()->shouldReturn(0);

    }

    public function it_is_countable(CursorInterface $cursor)
    {
        $this->shouldImplement('\Countable');

        $cursor->count()->shouldBeCalled()->willReturn(13);

        $this->shouldHaveCount(2);
    }
}

class Entity
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
