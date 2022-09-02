<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover;

use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover\LocaleProjectRemover;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover\ProjectRemoverInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;

class LocaleProjectRemoverSpec extends ObjectBehavior
{
    function let(
        ProjectRepositoryInterface $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith($projectRepository, $projectRemover, $detacher);
    }

    function it_is_a_project_remover()
    {
        $this->shouldHaveType(LocaleProjectRemover::class);
        $this->shouldImplement(ProjectRemoverInterface::class);
    }

    function it_removes_impacted_projects_if_the_locale_is_no_longer_part_of_its_channel_and_detach_others_projects(
        $projectRepository,
        $projectRemover,
        $detacher,
        ProjectInterface $firstProject,
        ProjectInterface $secondProject,
        LocaleInterface $locale,
        ChannelInterface $mobileChannel,
        ChannelInterface $printChannel
    ) {
        $projectRepository->findByLocale($locale)->willReturn([$firstProject, $secondProject]);

        $firstProject->getChannel()->willReturn($mobileChannel);
        $secondProject->getChannel()->willReturn($printChannel);

        $locale->getCode()->willReturn('en_US');
        $locale->isActivated()->willReturn(true);

        $mobileChannel->getLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $printChannel->getLocaleCodes()->willReturn(['fr_FR']);

        $projectRemover->remove($firstProject)->shouldNotBeCalled();
        $projectRemover->remove($secondProject)->shouldBeCalled();

        $detacher->detach($firstProject)->shouldBeCalled();
        $detacher->detach($secondProject)->shouldNotBeCalled();

        $this->removeProjectsImpactedBy($locale);
    }

    function it_removes_impacted_projects_if_the_locale_is_deactivated_and_detach_others_projects(
        $projectRepository,
        $projectRemover,
        $detacher,
        ProjectInterface $firstProject,
        ProjectInterface $secondProject,
        LocaleInterface $locale
    ) {
        $projectRepository->findByLocale($locale)->willReturn([$firstProject, $secondProject]);

        $locale->isActivated()->willReturn(false);

        $projectRemover->remove($firstProject)->shouldBeCalled();
        $projectRemover->remove($secondProject)->shouldBeCalled();

        $detacher->detach($firstProject)->shouldNotBeCalled();
        $detacher->detach($secondProject)->shouldNotBeCalled();

        $this->removeProjectsImpactedBy($locale);
    }

    function it_removes_impacted_projects_only_for_locale_post_save(
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $this->isSupported($channel, StorageEvents::PRE_REMOVE)->shouldReturn(false);
        $this->isSupported($channel, StorageEvents::POST_SAVE)->shouldReturn(false);
        $this->isSupported($locale, StorageEvents::PRE_REMOVE)->shouldReturn(false);
        $this->isSupported($locale, StorageEvents::POST_SAVE)->shouldReturn(true);
    }
}
