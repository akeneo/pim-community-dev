<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ChainedProjectRemover;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverInterface;

class ChainedProjectRemoverSpec extends ObjectBehavior
{
    function let(ProjectRemoverInterface $channelRemover, ProjectRemoverInterface $localeRemover)
    {
        $this->beConstructedWith([$channelRemover, $localeRemover]);
    }

    function it_is_a_chained_project_remover()
    {
        $this->shouldHaveType(ChainedProjectRemover::class);
        $this->shouldImplement(ProjectRemoverInterface::class);
    }

    function it_asks_each_removers_to_remove_impacted_projects(
        $channelRemover,
        $localeRemover,
        ChannelInterface $channel
    ) {
        $channelRemover->removeProjectsImpactedBy($channel)->shouldBeCalled();
        $localeRemover->removeProjectsImpactedBy($channel)->shouldBeCalled();

        $this->removeProjectsImpactedBy($channel);
    }
}
