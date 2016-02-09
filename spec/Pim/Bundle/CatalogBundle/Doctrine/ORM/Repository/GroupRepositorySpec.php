<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\Query\Expr;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Prophecy\Promise\ReturnPromise;

class GroupRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $classMetadata)
    {
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\GroupRepository');
    }

    function it_checks_if_group_has_attribute_in_family(
        $em,
        QueryBuilder $queryBuilder1,
        QueryBuilder $queryBuilder2,
        AbstractQuery $query1,
        Expr $expr1
    ) {

        $em->createQueryBuilder()->willReturn($expr1);
        $em->createQueryBuilder()->willReturn($queryBuilder1);

        $queryBuilder1->expr()->willReturn($expr1);
        $expr1->in('g.id', [10, 12])->willReturn($expr1);
        $queryBuilder1->select('g')->willReturn($queryBuilder1);
        $queryBuilder1->select('COUNT(g.id)')->willReturn($queryBuilder1);
        $queryBuilder1->from(Argument::any(), 'g')->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.attributes', 'a')->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.type', 't')->willReturn($queryBuilder1);
        $queryBuilder1->where($expr1)->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('t.variant = :variant')->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('a.code = :code')->willReturn($queryBuilder1);
        $queryBuilder1->setMaxResults(1)->willReturn($queryBuilder1);
        $queryBuilder1->setParameters([
            'variant' => true,
            'code' => 'attribute_code',
        ])->willReturn($queryBuilder1);

        $queryBuilder1->getQuery()->willReturn($query1);
        $query1->getSingleResult()->willReturn(1);

        $this->hasAttribute([10, 12], 'attribute_code')->shouldReturn(true);
    }

    function it_checks_if_group_has_attribute_in_product_template(
        $em,
        QueryBuilder $queryBuilder1,
        QueryBuilder $queryBuilder2,
        AbstractQuery $query1,
        AbstractQuery $query2,
        Expr $expr1,
        Expr $expr2
    ) {

        $em->createQueryBuilder()->will(new ReturnPromise([$expr1, $expr2]));
        $em->createQueryBuilder()->will(new ReturnPromise([$queryBuilder1, $queryBuilder2]));

        $queryBuilder1->expr()->willReturn($expr1);
        $expr1->in('g.id', [10, 12])->willReturn($expr1);
        $queryBuilder1->select('g')->willReturn($queryBuilder1);
        $queryBuilder1->select('COUNT(g.id)')->willReturn($queryBuilder1);
        $queryBuilder1->from(Argument::any(), 'g')->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.attributes', 'a')->willReturn($queryBuilder1);
        $queryBuilder1->leftJoin('g.type', 't')->willReturn($queryBuilder1);
        $queryBuilder1->where($expr1)->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('t.variant = :variant')->willReturn($queryBuilder1);
        $queryBuilder1->andWhere('a.code = :code')->willReturn($queryBuilder1);
        $queryBuilder1->setMaxResults(1)->willReturn($queryBuilder1);
        $queryBuilder1->setParameters([
            'variant' => true,
            'code' => 'attribute_code',
        ])->willReturn($queryBuilder1);

        $queryBuilder1->getQuery()->willReturn($query1);
        $query1->getSingleResult()->willReturn(0);

        $queryBuilder2->expr()->willReturn($expr2);
        $expr2->in('g.id', [10, 12])->willReturn($expr2);
        $queryBuilder2->select('g')->willReturn($queryBuilder2);
        $queryBuilder2->select('pt.valuesData')->willReturn($queryBuilder2);
        $queryBuilder2->from(Argument::any(), 'g')->willReturn($queryBuilder2);
        $queryBuilder2->leftJoin('g.type', 't')->willReturn($queryBuilder2);
        $queryBuilder2->leftJoin('g.productTemplate', 'pt')->willReturn($queryBuilder2);
        $queryBuilder2->where($expr2)->willReturn($queryBuilder2);
        $queryBuilder2->andWhere('t.variant = :variant')->willReturn($queryBuilder2);
        $queryBuilder2->setParameters([
            'variant' => true,
            'id' => 10,
        ])->willReturn($queryBuilder2);

        $queryBuilder2->getQuery()->willReturn($query2);
        $query2->getArrayResult()->willReturn(['attribute_code' => '...']);

        $this->hasAttribute([10, 12], 'attribute_code')->shouldReturn(true);
    }
}
