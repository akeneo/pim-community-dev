<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Cursor;

use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface;

class PaginatorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let()
    {
        $this->beConstructedWith('Akeneo\Bundle\StorageUtilsBundle\Cursor\Paginator', self::DEFAULT_BATCH_SIZE);
    }

    function it_is_a_paginator_factory()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorFactory');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorFactoryInterface');
    }

    function it_creates_a_paginator(CursorInterface $cursor)
    {
        $paginator = $this->createPaginator($cursor);
        $paginator->shouldBeAnInstanceOf('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorInterface');
    }
}
