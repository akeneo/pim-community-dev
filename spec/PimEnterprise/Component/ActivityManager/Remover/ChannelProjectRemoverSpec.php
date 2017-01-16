<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ChannelProjectRemover;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;

class ChannelProjectRemoverSpec extends ObjectBehavior
{
    function let(
        ProjectRepositoryInterface $projectRepository,
        RemoverInterface $projectRemover
    ) {
        $this->beConstructedWith($projectRepository, $projectRemover);
    }

    function it_is_a_project_remover_rule()
    {
        $this->shouldHaveType(ChannelProjectRemover::class);
        $this->shouldImplement(ProjectRemoverInterface::class);
    }

    function it_removes_impacted_project_in_terms_of_a_channel_and_detach_other_projects(
        $projectRepository,
        $projectRemover,
        ProjectInterface $mobileProject,
        ChannelInterface $mobileChannel
    ) {
        $mobileChannel->getCode()->willReturn('mobile');

        $mobileProject->getChannel()->willReturn($mobileChannel);

        $projectRepository->findByChannel($mobileChannel)->willReturn([$mobileProject]);

        $projectRemover->remove($mobileProject)->shouldBeCalled();

        $this->removeProjectsImpactedBy($mobileChannel);
    }

    function it_removes_impacted_project_only_in_terms_of_a_channel(
        $projectRepository,
        LocaleInterface $locale
    ) {
        $locale->getCode()->shouldNotBeCalled();
        $projectRepository->findByChannel()->shouldNotBeCalled();

        $this->removeProjectsImpactedBy($locale);
    }
}
