<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ORM;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Asset\Component\Model\ChannelVariationsConfigurationInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\Repository\ChannelConfigurationRepositoryInterface;
use Akeneo\Asset\Component\Repository\VariationRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveChannelSubscriberSpec extends ObjectBehavior
{
    function let(
        VariationRepositoryInterface $variationRepo,
        ChannelConfigurationRepositoryInterface $channelConfigRepo,
        RemoverInterface $variationRemover,
        RemoverInterface $channelConfigRemover
    ) {
        $this->beConstructedWith($variationRepo, $channelConfigRepo, $variationRemover, $channelConfigRemover);
    }

    function it_is_an_event_subsriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_returns_the_events_it_subscribed_to()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'removeChannelLinkedEntities',
        ]);
    }

    function it_removes_related_entities_before_a_channel_deletion(
        $variationRepo,
        $channelConfigRepo,
        $variationRemover,
        $channelConfigRemover,
        GenericEvent $event,
        ChannelInterface $channel,
        VariationInterface $webVariation,
        VariationInterface $mobileVariation,
        ChannelVariationsConfigurationInterface $webChannelConfig,
        ChannelVariationsConfigurationInterface $mobChannelConfig
    ) {
        $channel->getId()->willReturn(66);
        $event->getSubject()->willReturn($channel);

        $variationRepo->findBy(['channel' => 66])->willReturn([$webVariation, $mobileVariation]);
        $variationRemover->remove($webVariation)->shouldBeCalled();
        $variationRemover->remove($mobileVariation)->shouldBeCalled();

        $channelConfigRepo->findBy(['channel' => 66])->willReturn([$webChannelConfig, $mobChannelConfig]);
        $channelConfigRemover->remove($webChannelConfig)->shouldBeCalled();
        $channelConfigRemover->remove($mobChannelConfig)->shouldBeCalled();

        $this->removeChannelLinkedEntities($event)->shouldReturn(null);
    }

    function it_does_nothing_if_the_channel_does_not_have_the_right_inteface(GenericEvent $event)
    {
        $event->getSubject()->willReturn(new \stdClass);

        $this->removeChannelLinkedEntities($event)->shouldReturn(null);
    }
}
