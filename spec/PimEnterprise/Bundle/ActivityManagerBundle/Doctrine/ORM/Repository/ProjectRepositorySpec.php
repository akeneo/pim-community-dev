<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository\ProjectRepository;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
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

    function it_is_an_object_identifiable_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_searchable_repository()
    {
        $this->shouldImplement(SearchableRepositoryInterface::class);
    }

    function its_identifier_is_id()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }
}
