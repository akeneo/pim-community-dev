<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Remover;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Remover\ChainedProjectRemover;
use PimEnterprise\Component\TeamworkAssistant\Remover\ProjectRemoverInterface;

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
        $channelRemover->isSupported($channel, StorageEvents::PRE_REMOVE)->willReturn(true);
        $channelRemover->removeProjectsImpactedBy($channel, StorageEvents::PRE_REMOVE)->shouldBeCalled();
        $localeRemover->isSupported($channel, StorageEvents::PRE_REMOVE)->willReturn(false);
        $localeRemover->removeProjectsImpactedBy($channel, StorageEvents::PRE_REMOVE)->shouldNotBeCalled();

        $this->removeProjectsImpactedBy($channel, StorageEvents::PRE_REMOVE);
    }

    function it_is_always_supported(AttributeInterface $attribute, ChannelInterface $channel)
    {
        $this->isSupported($attribute, StorageEvents::PRE_REMOVE)->shouldReturn(true);
        $this->isSupported($attribute, StorageEvents::POST_SAVE)->shouldReturn(true);
        $this->isSupported($attribute)->shouldReturn(true);
        $this->isSupported($channel, StorageEvents::PRE_REMOVE)->shouldReturn(true);
        $this->isSupported($channel, StorageEvents::POST_SAVE)->shouldReturn(true);
        $this->isSupported($channel)->shouldReturn(true);
    }
}
