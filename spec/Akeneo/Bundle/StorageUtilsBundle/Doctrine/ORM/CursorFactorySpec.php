<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

class CursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith(
            'Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor',
            $entityManager,
            self::DEFAULT_BATCH_SIZE
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\CursorFactory');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorFactoryInterface');
    }

    function it_create_a_cursor(QueryBuilder $queryBuilder)
    {
        $cursor = $this->createCursor($queryBuilder);
        $cursor->shouldBeAnInstanceOf('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }
}
