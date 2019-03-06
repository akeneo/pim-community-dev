<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Entity\Repository;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository as BaseJobExecutionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use PhpSpec\ObjectBehavior;

class JobExecutionRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $metadata)
    {
        $this->beConstructedWith($em, 'class');
        $em->getClassMetadata('class')->willReturn($metadata);
    }

    function it_is_a_job_execution_repository()
    {
        $this->shouldImplement(DatagridRepositoryInterface::class);
        $this->shouldBeAnInstanceOf(EntityRepository::class);
        $this->shouldBeAnInstanceOf(BaseJobExecutionRepository::class);
    }
}
