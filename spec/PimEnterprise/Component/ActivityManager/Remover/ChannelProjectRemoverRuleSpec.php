<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ChannelProjectRemoverRule;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverRuleInterface;

class ChannelProjectRemoverRuleSpec extends ObjectBehavior
{
    function it_is_a_project_remover_rule()
    {
        $this->shouldHaveType(ChannelProjectRemoverRule::class);
        $this->shouldImplement(ProjectRemoverRuleInterface::class);
    }

    function it_defines_if_a_project_has_to_be_removed_in_terms_of_a_channel(
        ProjectInterface $printProject,
        ProjectInterface $mobileProject,
        ChannelInterface $printChannel,
        ChannelInterface $mobileChannel
    ) {
        $printProject->getChannel()->willReturn($printChannel);
        $mobileProject->getChannel()->willReturn($mobileChannel);
        $printChannel->getCode()->willReturn('print');
        $mobileChannel->getCode()->willReturn('mobile');

        $this->hasToBeRemoved($printProject, $mobileChannel)->shouldReturn(false);
        $this->hasToBeRemoved($mobileProject, $mobileChannel)->shouldReturn(true);
    }

    function it_defines_that_a_project_has_not_to_be_removed_for_another_entity(
        ProjectInterface $project,
        LocaleInterface $locale
    ) {
        $project->getChannel()->shouldNotBeCalled();
        $locale->getCode()->shouldNotBeCalled();

        $this->hasToBeRemoved($project, $locale)->shouldReturn(false);
    }
}
