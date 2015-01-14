<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Cursor;

use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface;

class PaginatorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    public function let()
    {
        $this->beConstructedWith('Akeneo\Bundle\StorageUtilsBundle\Cursor\Paginator', self::DEFAULT_BATCH_SIZE);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorFactory');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorFactoryInterface');
    }

    public function it_create_a_paginator(CursorInterface $cursor)
    {
        $paginator = $this->createPaginator($cursor);
        $paginator->shouldBeAnInstanceOf('Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorInterface');
    }
}
