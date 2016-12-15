<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository\ProjectCompletenessRepository;
use Akeneo\ActivityManager\Component\Repository\ProjectCompletenessRepositoryInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

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
