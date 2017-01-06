<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ChainedProjectRemoverRule;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverRuleInterface;

class ChainedProjectRemoverRuleSpec extends ObjectBehavior
{
    function let(ProjectRemoverRuleInterface $channelRemover, ProjectRemoverRuleInterface $localeRemover)
    {
        $this->beConstructedWith([$channelRemover, $localeRemover]);
    }

    function it_is_a_chained_project_remover_rule()
    {
        $this->shouldHaveType(ChainedProjectRemoverRule::class);
        $this->shouldImplement(ProjectRemoverRuleInterface::class);
    }

    function it_defines_if_a_project_has_to_be_removed(
        $channelRemover,
        $localeRemover,
        ProjectInterface $project,
        ChannelInterface $channel
    ) {
        $channelRemover->hasToBeRemoved($project, $channel)->shouldBeCalled()->willReturn(true);
        $localeRemover->hasToBeRemoved($project, $channel)->shouldNotBeCalled();

        $this->hasToBeRemoved($project, $channel)->shouldReturn(true);
    }

    function it_defines_if_a_project_has_not_to_be_removed(
        $channelRemover,
        $localeRemover,
        ProjectInterface $project,
        LocaleInterface $locale
    ) {
        $channelRemover->hasToBeRemoved($project, $locale)->shouldBeCalled()->willReturn(false);
        $localeRemover->hasToBeRemoved($project, $locale)->shouldBeCalled()->willReturn(false);

        $this->hasToBeRemoved($project, $locale)->shouldReturn(false);
    }
}
