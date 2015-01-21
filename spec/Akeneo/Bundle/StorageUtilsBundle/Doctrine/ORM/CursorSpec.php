<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\ModelRepositoryInterface;
use Prophecy\Argument;
use Doctrine\ORM\Query\Expr\From;

class CursorSpec extends ObjectBehavior
{
    const PAGE_SIZE = 10;

    function let(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager
    ) {
        $this->beConstructedWith($queryBuilder, $entityManager, self::PAGE_SIZE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }

    function it_is_countable($queryBuilder, AbstractQuery $query, From $from)
    {
        $this->shouldImplement('\Countable');

        $rootIdExpr = 'o.id';

        $from->getFrom()->shouldBeCalled()->willReturn('Pim\Bundle\CatalogBundle\Model\Product');
        $from->getAlias()->shouldBeCalled()->willReturn('o');

        $queryBuilder->getRootAliases()->shouldBeCalled()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->shouldBeCalled()->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(), Argument::any(), $rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled()->willReturn([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]);

        $this->shouldHaveCount(13);
    }

    function it_is_iterable(
        $queryBuilder,
        EntityManager $entityManager,
        AbstractQuery $query,
        From $from,
        ModelRepositoryInterface $repository
    ) {
        $this->shouldImplement('\Iterator');

        $page1 = [
            new Entity(10),
            new Entity(11),
            new Entity(12),
            new Entity(13),
            new Entity(14),
            new Entity(15),
            new Entity(16),
            new Entity(17),
            new Entity(18),
            new Entity(19)
        ];
        $page2 = [new Entity(20), new Entity(21), new Entity(22)];
        $data = array_merge($page1, $page2);

        $rootIdExpr = 'o.id';
        $entityClass = 'Pim\Bundle\CatalogBundle\Model\Product';

        $from->getFrom()->shouldBeCalled()->willReturn($entityClass);
        $from->getAlias()->shouldBeCalled()->willReturn('o');

        $queryBuilder->getRootAliases()->shouldBeCalled()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->shouldBeCalled()->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(), Argument::any(), $rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled()->willReturn([
            10 => 10,
            11 => 11,
            12 => 12,
            13 => 13,
            14 => 14,
            15 => 15,
            16 => 16,
            17 => 17,
            18 => 18,
            19 => 19,
            20 => 20,
            21 => 21,
            22 => 22
        ]);

        $entityManager->getRepository($entityClass)->shouldBeCalled()->willReturn($repository);
        $repository->findByIds([10, 11, 12, 13, 14, 15, 16, 17, 18, 19])->shouldBeCalled()->willReturn($page1);
        $repository->findByIds([20, 21, 22])->shouldBeCalled()->willReturn($page2);

        // methods that not iterate can be called twice
        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($data[0]);
        $this->current()->shouldReturn($data[0]);
        $this->key()->shouldReturn(0);
        $this->key()->shouldReturn(0);

        // for each call sequence for 13 items
        $this->rewind()->shouldReturn(null);
        for ($i = 0; $i < 13; $i++) {
            if ($i > 0) {
                $this->next()->shouldReturn(null);
            }
            $this->valid()->shouldReturn(true);
            $this->current()->shouldReturn($data[$i]);
            $this->key()->shouldReturn($i);
        }

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(false);

        // check behaviour after the end of data
        $this->current()->shouldReturn(false);
        $this->key()->shouldReturn(null);
    }

    function it_check_entity_repository($queryBuilder, AbstractQuery $query, From $from)
    {
        $rootIdExpr = 'o.id';

        $from->getFrom()->shouldBeCalled()->willReturn('Pim\Bundle\CatalogBundle\Model\Product');
        $from->getAlias()->shouldBeCalled()->willReturn('o');

        $queryBuilder->getRootAliases()->shouldBeCalled()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->shouldBeCalled()->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(), Argument::any(), $rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled()->willReturn([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]);

        $this->shouldThrow('\Exception')->duringRewind();
    }
}

class Entity
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
