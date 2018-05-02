<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\CursorFactory;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CursorFactorySpec extends ObjectBehavior
{
    const DEFAULT_BATCH_SIZE = 100;

    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith(
            Cursor::class,
            $entityManager,
            self::DEFAULT_BATCH_SIZE
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CursorFactory::class);
        $this->shouldImplement(CursorFactoryInterface::class);
    }

    function it_creates_a_cursor($entityManager, QueryBuilder $queryBuilder, QueryWithCache $query, From $from)
    {
        $queryBuilder->getRootAliases()->willReturn(['a']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select('a.id')->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(), Argument::any(), 'a.id')->willReturn($queryBuilder);
        $queryBuilder->distinct(true)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->useQueryCache(false)->shouldBeCalled();
        $query->getArrayResult()->willReturn([]);

        $this->createCursor($queryBuilder)->shouldBeLike(
            new Cursor($queryBuilder->getWrappedObject(), $entityManager->getWrappedObject(), 100)
        );
    }
}

abstract class QueryWithCache extends AbstractQuery
{
    public abstract function useQueryCache($bool);
}
