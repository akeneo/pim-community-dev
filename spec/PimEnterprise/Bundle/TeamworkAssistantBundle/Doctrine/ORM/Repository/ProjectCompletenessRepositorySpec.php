<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository\ProjectCompletenessRepository;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\TableNameMapper;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface;

class ProjectCompletenessRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, TableNameMapper $tableNameMapper)
    {
        $this->beConstructedWith($entityManager, $tableNameMapper);
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
