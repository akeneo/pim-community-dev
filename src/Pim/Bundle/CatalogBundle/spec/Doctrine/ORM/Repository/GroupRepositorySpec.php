<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;

class GroupRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'group';

        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_group_repository()
    {
        $this->shouldImplement('Pim\Component\Catalog\Repository\GroupRepositoryInterface');
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\GroupRepository');
    }

    function it_checks_if_group_has_attribute_in_family(
        $em,
        QueryBuilder $queryBuilder1,
        AbstractQuery $query1
    ) {
        $em->createQueryBuilder()->willReturn($queryBuilder1);

        $queryBuilder1->select('g')->willReturn($queryBuilder1);
        $queryBuilder1->from(Argument::any(), 'g', null)->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.axisAttributes', 'a')->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.type', 't')->willReturn($queryBuilder1);
        $queryBuilder1->where('g.id IN (:ids)')->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('t.variant = :variant')->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('a.code = :code')->willReturn($queryBuilder1);
        $queryBuilder1->setMaxResults(1)->willReturn($queryBuilder1);
        $queryBuilder1->setParameters([
            'variant' => true,
            'code' => 'attribute_code',
            'ids' => [10, 12],
        ])->willReturn($queryBuilder1);

        $queryBuilder1->getQuery()->willReturn($query1);
        $query1->getArrayResult()->willReturn([10]);

        $this->hasAttribute([10, 12], 'attribute_code')->shouldReturn(true);
    }

    function it_checks_if_group_has_attribute_in_product_template(
        $em,
        QueryBuilder $queryBuilder1,
        QueryBuilder $queryBuilder2,
        AbstractQuery $query1,
        AbstractQuery $query2
    ) {
        $em->createQueryBuilder()->will(new ReturnPromise([$queryBuilder1, $queryBuilder2]));

        $queryBuilder1->select('g')->willReturn($queryBuilder1);
        $queryBuilder1->from(Argument::any(), 'g', null)->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.axisAttributes', 'a')->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.type', 't')->willReturn($queryBuilder1);
        $queryBuilder1->where('g.id IN (:ids)')->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('t.variant = :variant')->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('a.code = :code')->willReturn($queryBuilder1);
        $queryBuilder1->setMaxResults(1)->willReturn($queryBuilder1);
        $queryBuilder1->setParameters([
            'variant' => true,
            'code' => 'attribute_code',
            'ids' => [10, 12],
        ])->willReturn($queryBuilder1);

        $queryBuilder1->getQuery()->willReturn($query1);
        $query1->getArrayResult()->willReturn([]);

        $queryBuilder2->select('g')->willReturn($queryBuilder2);
        $queryBuilder2->select('pt.valuesData')->willReturn($queryBuilder2);
        $queryBuilder2->from(Argument::any(), 'g', null)->willReturn($queryBuilder2);
        $queryBuilder2->leftJoin('g.type', 't')->willReturn($queryBuilder2);
        $queryBuilder2->leftJoin('g.productTemplate', 'pt')->willReturn($queryBuilder2);
        $queryBuilder2->where('g.id IN (:ids)')->willReturn($queryBuilder2);
        $queryBuilder2->andWhere('t.variant = :variant')->willReturn($queryBuilder2);
        $queryBuilder2->setParameters([
            'variant' => true,
            'ids' => [10, 12],
        ])->willReturn($queryBuilder2);

        $queryBuilder2->getQuery()->willReturn($query2);
        $query2->getArrayResult()->willReturn([
            ['valuesData' => ['foo' => 'val1', 'attribute_code' => 'val2']],
            ['valuesData' => ['foo' => 'val3', 'bar' => 'val4']]
        ]);
        $this->hasAttribute([10, 12], 'attribute_code')->shouldReturn(true);
    }
}
