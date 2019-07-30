<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\ProjectCompletenessRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;

class ProjectCompletenessRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCompletenessRepository::class);
    }

    function it_is_a_project_completeness_repository()
    {
        $this->shouldImplement(ProjectCompletenessRepositoryInterface::class);
    }
}
