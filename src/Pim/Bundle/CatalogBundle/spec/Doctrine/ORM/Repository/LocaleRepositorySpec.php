<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

class LocaleRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->name = 'locale';
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_locale_repository()
    {
        $this->shouldImplement('Pim\Component\Catalog\Repository\LocaleRepositoryInterface');
    }

    function it_count_all_activated_locales($em, QueryBuilder $queryBuilder, AbstractQuery $query, Expr $expr)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('l')->willReturn($queryBuilder);
        $queryBuilder->from('locale', 'l', null)->willReturn($queryBuilder);
        $queryBuilder->select('COUNT(l.id)')->willReturn($queryBuilder);
        $queryBuilder->expr()->willReturn($expr);
        $expr->eq('l.activated', true)->willReturn($expr);
        $queryBuilder->where($expr)->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled();

        $this->countAllActivated();
    }
}
