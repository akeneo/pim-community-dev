<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\LocaleSubscriber;
use Pim\Component\Catalog\LocaleEvents;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class LocaleSubscriberSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleSubscriber::class);
    }

    function it_is_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'localeStatus',
        ]);
    }

    function it_dispatches_an_event_when_locale_is_activated(
        $eventDispatcher,
        GenericEvent $event,
        LocaleInterface $locale,
        ArrayCollection $channels
    ) {
        $event->getSubject()->willReturn($locale);
        $locale->getChannels()->willReturn($channels);
        $channels->count()->willReturn(5);
        $locale->setActivated(true)->shouldBeCalled();

        $eventDispatcher->dispatch(
            LocaleEvents::LOCALE_ACTIVATED,
            Argument::type(GenericEvent::class)
        )->shouldBeCalled();

        $this->localeStatus($event)->shouldReturn(null);
    }

    function it_dispatches_an_event_when_locale_is_deactivated(
        $eventDispatcher,
        GenericEvent $event,
        LocaleInterface $locale,
        ArrayCollection $channels
    ) {
        $event->getSubject()->willReturn($locale);
        $locale->getChannels()->willReturn($channels);
        $channels->count()->willReturn(0);
        $locale->setActivated(false)->shouldBeCalled();

        $eventDispatcher->dispatch(
            LocaleEvents::LOCALE_DEACTIVATED,
            Argument::type(GenericEvent::class)
        )->shouldBeCalled();

        $this->localeStatus($event)->shouldReturn(null);
    }
}
