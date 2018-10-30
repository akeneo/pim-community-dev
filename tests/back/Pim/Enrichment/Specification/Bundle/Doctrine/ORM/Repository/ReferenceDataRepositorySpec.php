<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ReferenceDataRepository;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferenceDataRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataRepository::class);
    }

    function let(
        EntityManager $em,
        Connection $connection
    ) {
        $classMetadata = new ClassMetadata(Color::class);
        $classMetadata->mapField([
            'fieldName' => 'sortOrder',
            'type' => 'integer',
        ]);
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_finds_the_reference_data_for_an_empty_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'rd.id as id, ' .
            'CASE WHEN rd.name IS NULL OR rd.name = \'\' THEN CONCAT(\'[\', rd.code, \']\') ELSE rd.name END AS text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('rd')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('rd.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('rd.code')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch();
    }

    function it_finds_the_reference_data_for_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'rd.id as id, ' .
            'CASE WHEN rd.name IS NULL OR rd.name = \'\' THEN CONCAT(\'[\', rd.code, \']\') ELSE rd.name END AS text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('rd')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('rd.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('rd.code')->willReturn($qb);
        $qb->andWhere('rd.code LIKE :search OR rd.name LIKE :search')->willReturn($qb);
        $qb->setParameter('search', '%my-search%')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch('my-search');
    }

    function it_finds_the_reference_data_third_page_of_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'rd.id as id, ' .
            'CASE WHEN rd.name IS NULL OR rd.name = \'\' THEN CONCAT(\'[\', rd.code, \']\') ELSE rd.name END AS text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('rd')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('rd.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('rd.code')->willReturn($qb);
        $qb->andWhere('rd.code LIKE :search OR rd.name LIKE :search')->willReturn($qb);
        $qb->setParameter('search', '%my-search%')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();
        $qb->setMaxResults(15)->willReturn($qb);
        $qb->setFirstResult(30)->willReturn($qb);

        $this->findBySearch('my-search', ['limit' => 15, 'page' => 3]);
    }

    function it_finds_and_sort_the_reference_data_by_code_only($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $classMetadata = new ClassMetadata(Color::class);
        $this->beConstructedWith($em, $classMetadata);

        $select = 'rd.id as id, ' .
            'CASE WHEN rd.name IS NULL OR rd.name = \'\' THEN CONCAT(\'[\', rd.code, \']\') ELSE rd.name END AS text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('rd')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('rd.code')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch();
    }
}
