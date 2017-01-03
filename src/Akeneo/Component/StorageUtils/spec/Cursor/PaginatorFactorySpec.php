<?php

namespace spec\Akeneo\Component\StorageUtils\Cursor;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class PaginatorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let()
    {
        $this->beConstructedWith('Akeneo\Component\StorageUtils\Cursor\Paginator', self::DEFAULT_BATCH_SIZE);
    }

    function it_is_a_paginator_factory()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Cursor\PaginatorFactory');
        $this->shouldImplement('Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface');
    }

    function it_creates_a_paginator(CursorInterface $cursor)
    {
        $paginator = $this->createPaginator($cursor);
        $paginator->shouldBeAnInstanceOf('Akeneo\Component\StorageUtils\Cursor\PaginatorInterface');
    }
}
