<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM;

use PhpSpec\ObjectBehavior;
use ArrayIterator;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\AbstractCursor;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\EntityRepositoryInterface;
use Prophecy\Argument;


class ORMCursorSpec extends ObjectBehavior
{
    const PAGE_SIZE=13;

    public function let(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        EntityRepositoryInterface $repository
    )
    {
        $this->beConstructedWith($queryBuilder, $entityManager, $repository, self::PAGE_SIZE);


    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM\ORMCursor');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }

    public function it_is_countable($queryBuilder, EntityManager $em)
    {
        $this->shouldImplement('\Countable');

        $query = new Query($em);

        $rootIdExpr = 'root.id';

        $queryBuilder->select($rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $queryBuilder->from(Argument::any(),Argument::any(),$rootIdExpr)->willReturn($queryBuilder);
        $queryBuilder->groupBy('root.id')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $this->shouldHaveCount(13);
    }

    public function it_is_a_iterable()
    {
        $this->shouldImplement('\Iterator');

        //$this->entityManager->clear();

/*
        foreach($it as $key => $value) {
            var_dump($key, $value);
            echo "\n";
        }*/

    }

    /*public function it_load_entities_by_pages($queryBuilder)
    {



    }*/

}
