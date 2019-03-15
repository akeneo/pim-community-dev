<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JobTrackerRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $metadata)
    {
        $this->beConstructedWith($em, 'class');
        $em->getClassMetadata('class')->willReturn($metadata);
    }

    function it_is_a_datagrid_repository()
    {
        $this->shouldImplement(DatagridRepositoryInterface::class);
        $this->shouldBeAnInstanceOf(EntityRepository::class);
    }

    function it_creates_a_datagrid_query_builder($em, QueryBuilder $qb)
    {
        $em->createQueryBuilder()->willReturn($qb);
        $qb->select(Argument::any())->willReturn($qb);
        $qb->from(Argument::cetera())->willReturn($qb);
        $qb->addSelect(Argument::any())->willReturn($qb);
        $qb->innerJoin(Argument::cetera())->willReturn($qb);
        $qb->leftJoin(Argument::cetera())->willReturn($qb);
        $qb->groupBy(Argument::any())->willReturn($qb);

        $this->createDatagridQueryBuilder()->shouldReturn($qb);
    }
}
