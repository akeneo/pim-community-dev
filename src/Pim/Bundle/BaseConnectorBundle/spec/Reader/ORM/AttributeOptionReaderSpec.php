<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeOptionReaderSpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager
    ) {
        $this->beConstructedWith($entityManager, 'Pim\Bundle\CatalogBundle\Entity\AttributeOption');
    }

    function it_is_a_reader()
    {
        $this->shouldImplement('Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\Reader');
    }

    function it_creates_a_sorted_query(
        EntityManager $entityManager,
        EntityRepository $entityRepository,
        QueryBuilder $qb,
        AbstractQuery $query
    ) {
        $entityManager->getRepository(Argument::any())->willReturn($entityRepository);
        $entityRepository->createQueryBuilder('ao')->willReturn($qb)->shouldBeCalled();
        $qb->orderBy('ao.attribute')->willReturn($qb)->shouldBeCalled();
        $qb->addOrderBy('ao.sortOrder')->willReturn($qb)->shouldBeCalled();
        $qb->getQuery()->willReturn($query)->shouldBeCalled();

        $this->getQuery();
    }
}
