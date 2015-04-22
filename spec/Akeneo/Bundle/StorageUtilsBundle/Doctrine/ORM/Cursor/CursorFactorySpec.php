<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;

class CursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith(
            'Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor',
            $entityManager,
            self::DEFAULT_BATCH_SIZE
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\CursorFactory');
        $this->shouldImplement('Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface');
    }

    function it_create_a_cursor(QueryBuilder $queryBuilder)
    {
        $cursor = $this->createCursor($queryBuilder);
        $cursor->shouldBeAnInstanceOf('Akeneo\Component\StorageUtils\Cursor\CursorInterface');
    }
}
