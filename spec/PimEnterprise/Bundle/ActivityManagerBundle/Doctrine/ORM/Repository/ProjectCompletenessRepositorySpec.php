<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\NativeQueryBuilder;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\ProjectCompletenessRepository;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

class ProjectCompletenessRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, NativeQueryBuilder $nativeQueryBuilder)
    {
        $this->beConstructedWith($entityManager, $nativeQueryBuilder);
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
