<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\LocaleProjectRemover;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;

class LocaleProjectRemoverSpec extends ObjectBehavior
{
    function let(
        ProjectRepositoryInterface $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith($projectRepository, $projectRemover, $detacher);
    }

    function it_is_a_project_remover_rule()
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

    function it_removes_impacted_projects_only_for_locale($projectRepository, ChannelInterface $channel)
    {
        $projectRepository->findByLocale($channel)->shouldNotBeCalled();

        $this->removeProjectsImpactedBy($channel);
    }
}
