<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\ProjectRepository;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class ProjectRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('Project')->willReturn($classMetadata);

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

    function it_is_an_object_identifiable_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function its_identifier_is_id()
    {
        $this->getIdentifierProperties()->shouldReturn(['id']);
    }
}
