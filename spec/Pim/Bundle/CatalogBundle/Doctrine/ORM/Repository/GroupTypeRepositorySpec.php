<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function it_finds_group_types($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('g')->willReturn($queryBuilder);
        $queryBuilder->select('g.id, g.code, t.label, t.locale')->willReturn($queryBuilder);
        $queryBuilder->from('group_type', 'g')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('g.translations', 't')->willReturn($queryBuilder);
        $queryBuilder->andWhere('g.variant = :variant')->willReturn($queryBuilder);
        $queryBuilder->setParameter('variant', true)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            ['id' => 10, 'label' => 'group fr', 'code' => 'group_code', 'locale' => 'fr_FR'],
            ['id' => 10, 'label' => 'group en', 'code' => 'group_code', 'locale' => 'en_US'],
            ['id' => 11, 'label' => null, 'code' => 'group_other_code', 'locale' => 'fr_FR'],
        ]);

        $this->findTypes(true, 'en_US')->shouldReturn([
            11 => '[group_other_code]',
            10 => 'group en',
        ]);
    }

    function it_finds_group_type_ids($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('g')->willReturn($queryBuilder);
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

        $this->findTypeIds(true, 'en_US')->shouldReturn([
            10,
            101,
            11,
        ]);
    }
}
