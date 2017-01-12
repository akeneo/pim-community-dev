<?php

namespace spec\PimEnterprise\Component\ActivityManager\Remover;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\LocaleProjectRemoverRule;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverRuleInterface;

class LocaleProjectRemoverRuleSpec extends ObjectBehavior
{
    function it_is_a_project_remover_rule()
    {
        $this->shouldHaveType(LocaleProjectRemoverRule::class);
        $this->shouldImplement(ProjectRemoverRuleInterface::class);
    }

    function it_defines_that_project_has_not_to_be_removed_if_the_locale_does_not_belong_to_it(
        ProjectInterface $project,
        LocaleInterface $localeEN,
        LocaleInterface $localeFR
    ) {
        $project->getLocale()->willReturn($localeFR);
        $localeEN->getCode()->willReturn('en_US');
        $localeFR->getCode()->willReturn('fr_FR');

        $this->hasToBeRemoved($project, $localeEN)->shouldReturn(false);
    }

    function it_defines_that_project_has_to_be_removed_if_its_locale_is_no_longer_part_of_its_channel_locales(
        ProjectInterface $project,
        LocaleInterface $localeEN,
        ChannelInterface $channel
    ) {
        $project->getLocale()->willReturn($localeEN);
        $localeEN->getCode()->willReturn('en_US');
        $localeEN->isActivated()->willReturn(true);

        $project->getChannel()->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['fr_FR']);

        $this->hasToBeRemoved($project, $localeEN)->shouldReturn(true);
    }

    function it_defines_that_project_has_not_to_be_removed_if_its_locale_is_part_of_its_channel_locales(
        ProjectInterface $project,
        LocaleInterface $localeEN,
        ChannelInterface $channel
    ) {
        $project->getLocale()->willReturn($localeEN);
        $localeEN->getCode()->willReturn('en_US');
        $localeEN->isActivated()->willReturn(true);

        $project->getChannel()->willReturn($channel);
        $channel->getLocaleCodes()->willReturn(['en_US']);

        $this->hasToBeRemoved($project, $localeEN)->shouldReturn(false);
    }

    function it_defines_that_project_has_to_be_removed_if_its_locale_is_deactivated(
        ProjectInterface $project,
        LocaleInterface $locale
    ) {
        $project->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $locale->isActivated()->willReturn(false);

        $this->hasToBeRemoved($project, $locale)->shouldReturn(true);
    }

    function it_defines_that_a_project_has_not_to_be_removed_for_another_entity(
        ProjectInterface $project,
        ChannelInterface $channel
    ) {
        $this->hasToBeRemoved($project, $channel)->shouldReturn(false);
    }
}
