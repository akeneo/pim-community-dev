<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ChainedProjectRemoverRule;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverEngine;

class ProjectRemoverEngineSpec extends ObjectBehavior
{
    function let(
        ChainedProjectRemoverRule $chainedProjectRemover,
        ObjectRepository $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith($chainedProjectRemover, $projectRepository, $projectRemover, $detacher);
    }

    function it_is_a_project_remover_engine()
    {
        $this->shouldHaveType(ProjectRemoverEngine::class);
    }

    function it_removes_projects_in_terms_of_another_entity(
        $chainedProjectRemover,
        $projectRepository,
        $projectRemover,
        $detacher,
        ProjectInterface $firstProject,
        ProjectInterface $secondProject,
        ProjectInterface $thirdProject,
        ChannelInterface $channel
    ) {
        $projectRepository->findAll()->willReturn([$firstProject, $secondProject, $thirdProject]);
        $chainedProjectRemover->hasToBeRemoved($firstProject, $channel)->willReturn(true);
        $chainedProjectRemover->hasToBeRemoved($secondProject, $channel)->willReturn(false);
        $chainedProjectRemover->hasToBeRemoved($thirdProject, $channel)->willReturn(true);

        $projectRemover->remove($firstProject)->shouldBeCalled();
        $projectRemover->remove($thirdProject)->shouldBeCalled();
        $detacher->detach($secondProject)->shouldBeCalled();

        $this->remove($channel);
    }
}
