<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Remover;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Remover\ChannelProjectRemover;
use PimEnterprise\Component\TeamworkAssistant\Remover\ProjectRemoverInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;

class ChannelProjectRemoverSpec extends ObjectBehavior
{
    function let(
        ProjectRepositoryInterface $projectRepository,
        RemoverInterface $projectRemover
    ) {
        $this->beConstructedWith($projectRepository, $projectRemover);
    }

    function it_is_a_project_remover()
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

    function it_removes_impacted_project_only_in_terms_of_a_channel_removal(
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $this->isSupported($locale, StorageEvents::PRE_REMOVE)->shouldReturn(false);
        $this->isSupported($locale, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($channel, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($channel, StorageEvents::PRE_REMOVE)->shouldReturn(true);
    }
}
