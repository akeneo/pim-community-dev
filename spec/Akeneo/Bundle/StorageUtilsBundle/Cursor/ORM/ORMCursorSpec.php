<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\EntityRepositoryInterface;
use Prophecy\Argument;
use Doctrine\ORM\Query\Expr\From;

class ORMCursorSpec extends ObjectBehavior
{
    const PAGE_SIZE=10;

    public function let(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager
    )
    {
        $this->beConstructedWith($queryBuilder, $entityManager, self::PAGE_SIZE);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM\ORMCursor');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }

    public function it_is_countable($queryBuilder, AbstractQuery $query, From $from)
    {
        $this->shouldImplement('\Countable');

        $rootIdExpr = 'o.id';

        $from->getFrom()->shouldBeCalled()->willReturn('Pim\Bundle\CatalogBundle\Model\Product');
        $from->getAlias()->shouldBeCalled()->willReturn('o');

        $queryBuilder->getRootAliases()->shouldBeCalled()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->shouldBeCalled()->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(),Argument::any(),$rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled()->willReturn([1,2,3,4,5,6,7,8,9,10,11,12,13]);

        $this->shouldHaveCount(13);
    }

    public function it_is_iterable(
        $queryBuilder,
        EntityManager $entityManager,
        AbstractQuery $query,
        From $from,
        Entity $entity,
        EntityRepositoryInterface $repository
    ) {
        $this->shouldImplement('\Iterator');

        $rootIdExpr = 'o.id';
        $entityClass = 'Pim\Bundle\CatalogBundle\Model\Product';

        $from->getFrom()->shouldBeCalled()->willReturn($entityClass);
        $from->getAlias()->shouldBeCalled()->willReturn('o');

        $queryBuilder->getRootAliases()->shouldBeCalled()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->shouldBeCalled()->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(),Argument::any(),$rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled()->willReturn([1,2,3,4,5,6,7,8,9,10,11,12,13]);

        $entityManager->getRepository($entityClass)->shouldBeCalled()->willReturn($repository);
        $repository->findByIds(Argument::any())->shouldBeCalled()->willReturn([
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity,
                $entity
            ]);

        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($entity);
        $this->key()->shouldReturn(0);
        $this->next()->shouldReturn(null);
    }

    public function it_check_entity_repository($queryBuilder, AbstractQuery $query, From $from)
    {
        $rootIdExpr = 'o.id';

        $from->getFrom()->shouldBeCalled()->willReturn('Pim\Bundle\CatalogBundle\Model\Product');
        $from->getAlias()->shouldBeCalled()->willReturn('o');

        $queryBuilder->getRootAliases()->shouldBeCalled()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->shouldBeCalled()->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(),Argument::any(),$rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled()->willReturn([1,2,3,4,5,6,7,8,9,10,11,12,13]);

        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->shouldThrow('\Exception')->duringCurrent();
    }

}

class Entity{

}
