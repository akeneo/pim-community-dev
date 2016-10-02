<?php

namespace spec\Akeneo\Component\StorageUtils\Cursor;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class PaginatorSpec extends ObjectBehavior
{
    const PAGE_SIZE = 10;

    function let(CursorInterface $cursor)
    {
        $this->beConstructedWith($cursor, self::PAGE_SIZE);
    }

    function it_is_a_paginator()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Cursor\Paginator');
        $this->shouldImplement('Akeneo\Component\StorageUtils\Cursor\PaginatorInterface');
    }

    function it_iterate_by_page_over_cursor(CursorInterface $cursor)
    {
        $data = [
            new Entity(1),
            new Entity(2),
            new Entity(3),
            new Entity(4),
            new Entity(5),
            new Entity(6),
            new Entity(7),
            new Entity(8),
            new Entity(9),
            new Entity(10),
            new Entity(11),
            new Entity(12),
            new Entity(13)
        ];

        $page1 = array_slice($data, 0, 10);
        $page2 = array_slice($data, 10, 10);

        $iterator = new \ArrayIterator($data);

        $cursor->count()->will(function() use($iterator) {
            return $iterator->count();
        });
        $cursor->current()->will(function() use($iterator) {
            return $iterator->current();
        });
        $cursor->next()->will(function() use($iterator) {
            $iterator->next();
        });
        $cursor->key()->will(function() use($iterator) {
            return $iterator->key();
        });
        $cursor->valid()->will(function() use($iterator) {
            return $iterator->valid();
        });
        $cursor->rewind()->will(function() use($iterator) {
            $iterator->rewind();
        });

        // for each call sequence
        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturnAnInstanceOf(\Traversable::class);
        $this->key()->shouldReturn(0);

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturnAnInstanceOf(\Traversable::class);
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
        $this->current()->shouldReturnAnInstanceOf(\Traversable::class);
        $this->current()->shouldReturnAnInstanceOf(\Traversable::class);
        $this->key()->shouldReturn(0);
        $this->key()->shouldReturn(0);
    }

    function it_is_countable(CursorInterface $cursor)
    {
        $this->shouldImplement('\Countable');

        $cursor->count()->shouldBeCalled()->willReturn(13);

        // page size is 10 : so 1 page of 10 elements and a second of 3
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
