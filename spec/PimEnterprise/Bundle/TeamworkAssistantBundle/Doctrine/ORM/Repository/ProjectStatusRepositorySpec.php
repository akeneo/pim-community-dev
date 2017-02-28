<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository\ProjectStatusRepository;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectStatus;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectStatusRepositoryInterface;

class ProjectStatusRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata()->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, ProjectStatus::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectStatusRepository::class);
    }

    function it_is_an_object_identifiable_repository()
    {
        $this->shouldImplement(ProjectStatusRepositoryInterface::class);
    }
}
