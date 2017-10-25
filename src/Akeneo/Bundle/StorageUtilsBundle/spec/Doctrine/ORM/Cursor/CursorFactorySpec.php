<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor;

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

    function it_creates_a_cursor(QueryBuilder $queryBuilder, QueryWithCache $query, From $from)
    {
        $queryBuilder->getRootAliases()->willReturn(['a']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select('a.id')->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(), Argument::any(), 'a.id')->willReturn($queryBuilder);
        $queryBuilder->groupBy('a.id')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->useQueryCache(false)->shouldBeCalled();
        $query->getArrayResult()->willReturn([]);

        $cursor = $this->createCursor($queryBuilder);
        $cursor->shouldBeAnInstanceOf('Akeneo\Component\StorageUtils\Cursor\CursorInterface');
    }
}

abstract class QueryWithCache extends AbstractQuery
{
    public abstract function useQueryCache($bool);
}
