<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ORM;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use PimEnterprise\Component\ProductAsset\Persistence\DeleteVariationsForChannelId;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveChannelSubscriberSpec extends ObjectBehavior
{
    function let(
        VariationRepositoryInterface $variationRepo,
        ChannelConfigurationRepositoryInterface $channelConfigRepo,
        RemoverInterface $variationRemover,
        RemoverInterface $channelConfigRemover,
        DeleteVariationsForChannelId $deleteVariationsForChannelId
    ) {
        $this->beConstructedWith(
            $variationRepo,
            $channelConfigRepo,
            $variationRemover,
            $channelConfigRemover,
            $deleteVariationsForChannelId
        );
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
        $channelConfigRepo,
        $channelConfigRemover,
        $deleteVariationsForChannelId,
        GenericEvent $event,
        ChannelInterface $channel,
        ChannelVariationsConfigurationInterface $webChannelConfig,
        ChannelVariationsConfigurationInterface $mobChannelConfig
    ) {
        $channel->getId()->willReturn(66);
        $event->getSubject()->willReturn($channel);

        $deleteVariationsForChannelId->execute(66)->shouldBeCalled();

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
