<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->name = 'family';
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_family_repository()
    {
        $this->shouldImplement(FamilyRepositoryInterface::class);
    }

    function it_checks_if_family_has_attribute($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('f')->willReturn($queryBuilder);
        $queryBuilder->select(1)->willReturn($queryBuilder);
        $queryBuilder->from('family', 'f', null)->willReturn($queryBuilder);
        $queryBuilder->innerJoin('f.attributes', 'a')->willReturn($queryBuilder);
        $queryBuilder->where('f.id = :id')->willReturn($queryBuilder);
        $queryBuilder->andWhere('a.code = :code')->willReturn($queryBuilder);
        $queryBuilder->setMaxResults(1)->willReturn($queryBuilder);
        $queryBuilder->setParameters([
            'id' => 10,
            'code' => 'attribute_code',
        ])->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn(['id' => 12]);
        $this->hasAttribute(10, 'attribute_code')->shouldReturn(true);
        $query->getArrayResult()->willReturn([]);
        $this->hasAttribute(10, 'attribute_code')->shouldReturn(false);
    }
}
