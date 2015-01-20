<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Doctrine\ODM\MongoDB\Query\Builder;

class CursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let()
    {
        $this->beConstructedWith(
            'Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor',
            self::DEFAULT_BATCH_SIZE
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\CursorFactory');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorFactoryInterface');
    }

    function it_create_a_cursor(Builder $queryBuilder)
    {
        $cursor = $this->createCursor($queryBuilder);
        $cursor->shouldBeAnInstanceOf('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }
}
