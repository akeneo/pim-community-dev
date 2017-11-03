<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CursorSpec extends ObjectBehavior
{
    const PAGE_SIZE = 10;

    function let(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        AbstractQuery $query,
        From $from
    ) {
        $rootIdExpr = 'o.id';
        $from->getFrom()->willReturn('Pim\Component\Catalog\Model\Product');
        $from->getAlias()->willReturn('o');

        $queryBuilder->getRootAliases()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(), Argument::any(), $rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);

        $query->getArrayResult()->willReturn([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]);

        $this->beConstructedWith($queryBuilder, $entityManager, self::PAGE_SIZE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor');
        $this->shouldImplement('Akeneo\Component\StorageUtils\Cursor\CursorInterface');
    }

    function it_is_countable($entityManager, CursorableRepositoryInterface $repository)
    {
        $entityManager->getRepository(Argument::any())->willReturn($repository);
        $repository->findByIds(Argument::any())->willReturn(Argument::any());

        $this->shouldImplement('\Countable');
        $this->count()->shouldReturn(13);
    }

    function it_is_iterable(
        $queryBuilder,
        EntityManager $entityManager,
        AbstractQuery $query,
        From $from,
        CursorableRepositoryInterface $repository
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
        $entityClass = 'Pim\Component\Catalog\Model\Product';

        $from->getFrom()->shouldBeCalled()->willReturn($entityClass);
        $from->getAlias()->shouldBeCalled()->willReturn('o');

        $queryBuilder->getRootAliases()->willReturn(['o']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select($rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(), Argument::any(), $rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->groupBy($rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);

        $query->getArrayResult()->willReturn([
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

        $entityManager->getRepository($entityClass)->willReturn($repository);
        $repository->findByIds([10, 11, 12, 13, 14, 15, 16, 17, 18, 19])->willReturn($page1);
        $repository->findByIds([20, 21, 22])->willReturn($page2);

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

    function it_check_entity_repository($entityManager, ObjectRepository $repository)
    {
        $entityManager->getRepository(Argument::any())->willReturn($repository);

        $this->shouldThrow('\LogicException');
    }

    function it_keeps_order_with_object_hydration(
        $query,
        $entityManager,
        CursorableRepositoryInterface $repository
    ) {
        $entityClass = 'Pim\Component\Catalog\Model\Product';

        $ids = [
            5 => 15,
            3 => 13,
            1 => 11,
            2 => 12,
            4 => 14
        ];
        $query->getArrayResult()->willReturn($ids);

        $entityManager->getRepository($entityClass)->willReturn($repository);

        $entity1 = new Entity(1);
        $entity2 = new Entity(2);
        $entity3 = new Entity(3);
        $entity4 = new Entity(4);
        $entity5 = new Entity(5);

        $repository->findByIds(array_keys($ids))->willReturn([
            1 => $entity1,
            2 => $entity2,
            3 => $entity3,
            4 => $entity4,
            5 => $entity5,
        ]);

        $this->current()->shouldNotReturn($entity1);
        $this->current()->shouldReturn($entity5);
        $this->next();
        $this->current()->shouldReturn($entity3);
        $this->next();
        $this->current()->shouldReturn($entity1);
        $this->next();
        $this->current()->shouldReturn($entity2);
        $this->next();
        $this->current()->shouldReturn($entity4);
    }

    function it_keeps_order_with_array_hydration(
        $query,
        $entityManager,
        CursorableRepositoryInterface $repository
    ) {
        $entityClass = 'Pim\Component\Catalog\Model\Product';

        $ids = [
            5 => 15,
            3 => 13,
            1 => 11,
            2 => 12,
            4 => 14
        ];
        $query->getArrayResult()->willReturn($ids);

        $entityManager->getRepository($entityClass)->willReturn($repository);

        $repository->findByIds(array_keys($ids))->willReturn([
            1 => ['id'=> 1],
            2 => ['id'=> 2],
            3 => ['id'=> 3],
            4 => ['id'=> 4],
            5 => ['id'=> 5],
        ]);

        $this->current()->shouldNotReturn(['id'=> 1]);
        $this->current()->shouldReturn(['id'=> 5]);
        $this->next();
        $this->current()->shouldReturn(['id'=> 3]);
        $this->next();
        $this->current()->shouldReturn(['id'=> 1]);
    }
}

class Entity
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
