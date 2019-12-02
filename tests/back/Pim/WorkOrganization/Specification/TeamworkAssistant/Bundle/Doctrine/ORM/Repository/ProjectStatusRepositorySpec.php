<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\ProjectStatusRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectStatus;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectStatusRepositoryInterface;

class ProjectStatusRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata(ProjectStatus::class)->willReturn($classMetadata);

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
