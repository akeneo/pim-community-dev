<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Prophecy\Argument;

class ReferenceDataRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository');
    }

    function let(
        EntityManager $em,
        Connection $connection,
        ClassMetadata $classMetadata
    ) {
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_finds_the_reference_data_for_an_empty_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('rd')->willReturn($qb);
        $qb->select('rd.id as id, rd.code as text')->willReturn($qb);
        $qb->from(Argument::any(), Argument::any())->willReturn($qb);

        $qb->setMaxResults(ReferenceDataRepositoryInterface::LIMIT_IF_NO_SEARCH)->willReturn($qb);
        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch();
    }

    function it_finds_the_reference_data_for_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('rd')->willReturn($qb);
        $qb->select('rd.id as id, rd.code as text')->willReturn($qb);
        $qb->from(Argument::any(), Argument::any())->willReturn($qb);
        $qb->andWhere('rd.code LIKE :search')->willReturn($qb);
        $qb->setParameter('search', 'my-search%')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch('my-search');
    }

    function it_finds_the_reference_data_third_page_of_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('rd')->willReturn($qb);
        $qb->select('rd.id as id, rd.code as text')->willReturn($qb);
        $qb->from(Argument::any(), Argument::any())->willReturn($qb);
        $qb->andWhere('rd.code LIKE :search')->willReturn($qb);
        $qb->setParameter('search', 'my-search%')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();
        $qb->setMaxResults(15)->willReturn($qb);
        $qb->setFirstResult(30)->willReturn($qb);

        $this->findBySearch('my-search', ['limit' => 15, 'page' => 3]);
    }
}
