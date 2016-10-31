<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\ProjectRepository;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class ProjectRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata()->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'Project');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectRepository::class);
    }

    function it_is_a_project_repository()
    {
        $this->shouldImplement(ProjectRepositoryInterface::class);
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldImplement(ObjectRepository::class);
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }
}
