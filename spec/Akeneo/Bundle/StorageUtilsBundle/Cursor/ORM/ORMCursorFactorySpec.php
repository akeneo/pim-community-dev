<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

class ORMCursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    public function let(EntityManager $entityManager)
    {
        $this->beConstructedWith('Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM\ORMCursor', $entityManager,
            self::DEFAULT_BATCH_SIZE);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM\ORMCursorFactory');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorFactoryInterface');
    }

    public function it_create_a_cursor(QueryBuilder $queryBuilder)
    {
        $cursor = $this->createCursor($queryBuilder);
        $cursor->shouldBeAnInstanceOf('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }
}
