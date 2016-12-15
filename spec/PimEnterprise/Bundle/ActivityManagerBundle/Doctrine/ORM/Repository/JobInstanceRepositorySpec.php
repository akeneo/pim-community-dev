<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository;

use Akeneo\ActivityManager\Component\Repository\JobInstanceRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class JobInstanceRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('JobInstance')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'JobInstance', 'project_calculation');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository\JobInstanceRepository::class);
    }

    function it_is_job_instance_repository()
    {
        $this->shouldImplement(JobInstanceRepositoryInterface::class);
    }
}
