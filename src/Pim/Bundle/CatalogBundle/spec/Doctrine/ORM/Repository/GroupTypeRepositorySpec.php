<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class GroupTypeRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'group_type';

        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\GroupTypeRepository');
    }

    function it_is_a_group_type_repository()
    {
        $this->shouldImplement('Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface');
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_finds_group_type_ids($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('g.id')->willReturn($queryBuilder);
        $queryBuilder->from('group_type', 'g', 'g.id')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('g.translations', 't')->willReturn($queryBuilder);
        $queryBuilder->andWhere('g.variant = :variant')->willReturn($queryBuilder);
        $queryBuilder->setParameter('variant', true)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            10 => ['id' => 10],
            101 => ['id' => 101],
            11 => ['id' => 11],
        ]);

        $this->findTypeIds(true)->shouldReturn([
            10,
            101,
            11,
        ]);
    }
}
