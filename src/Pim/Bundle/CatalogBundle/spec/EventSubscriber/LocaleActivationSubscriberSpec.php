<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\LocaleActivationSubscriber;
use Pim\Component\Catalog\LocaleEvents;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class LocaleActivationSubscriberSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher, LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($eventDispatcher, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleActivationSubscriber::class);
    }

    function it_is_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'localeStatusDispatcher',
        ]);
    }

    function it_dispatches_an_event_when_locale_is_activated(
        $eventDispatcher,
        $localeRepository,
        GenericEvent $event,
        LocaleInterface $locale,
        ArrayCollection $channels
    ) {
        $event->getSubject()->willReturn($locale);
        $locale->getChannels()->willReturn($channels);
        $locale->getCode()->willReturn('fr_FR');
        $channels->count()->willReturn(5);

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US']);

        $eventDispatcher->dispatch(
            LocaleEvents::LOCALE_ACTIVATED,
            Argument::type(GenericEvent::class)
        )->shouldBeCalled();

        $this->localeStatusDispatcher($event)->shouldReturn(null);
    }

    function it_dispatches_an_event_when_locale_is_deactivated(
        $eventDispatcher,
        $localeRepository,
        GenericEvent $event,
        LocaleInterface $locale,
        ArrayCollection $channels
    ) {
        $event->getSubject()->willReturn($locale);
        $locale->getChannels()->willReturn($channels);
        $locale->getCode()->willReturn('fr_FR');
        $channels->count()->willReturn(0);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR']);

        $eventDispatcher->dispatch(
            LocaleEvents::LOCALE_DEACTIVATED,
            Argument::type(GenericEvent::class)
        )->shouldBeCalled();

        $this->localeStatusDispatcher($event)->shouldReturn(null);
    }

    function it_should_not_dispatch_an_event_if_locale_still_has_channels(
        $eventDispatcher,
        $localeRepository,
        GenericEvent $event,
        LocaleInterface $locale,
        ArrayCollection $channels
    ) {
        $event->getSubject()->willReturn($locale);
        $locale->getChannels()->willReturn($channels);
        $locale->getCode()->willReturn('fr_FR');
        $channels->count()->willReturn(1);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR']);

        $eventDispatcher->dispatch(
            LocaleEvents::LOCALE_DEACTIVATED,
            Argument::type(GenericEvent::class)
        )->shouldNotBeCalled();

        $this->localeStatusDispatcher($event)->shouldReturn(null);
    }

    function it_should_not_dispatch_an_deactivated_event_if_locale_was_already_deactivated(
        $eventDispatcher,
        $localeRepository,
        GenericEvent $event,
        LocaleInterface $locale,
        ArrayCollection $channels
    ) {
        $event->getSubject()->willReturn($locale);
        $locale->getChannels()->willReturn($channels);
        $locale->getCode()->willReturn('fr_FR');
        $channels->count()->willReturn(0);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US']);

        $eventDispatcher->dispatch(
            LocaleEvents::LOCALE_DEACTIVATED,
            Argument::type(GenericEvent::class)
        )->shouldNotBeCalled();

        $this->localeStatusDispatcher($event)->shouldReturn(null);
    }

    function it_should_not_dispatch_an_activated_event_if_locale_was_already_activated(
        $eventDispatcher,
        $localeRepository,
        GenericEvent $event,
        LocaleInterface $locale,
        ArrayCollection $channels
    ) {
        $event->getSubject()->willReturn($locale);
        $locale->getChannels()->willReturn($channels);
        $locale->getCode()->willReturn('fr_FR');
        $channels->count()->willReturn(1);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR']);

        $eventDispatcher->dispatch(
            LocaleEvents::LOCALE_ACTIVATED,
            Argument::type(GenericEvent::class)
        )->shouldNotBeCalled();

        $this->localeStatusDispatcher($event)->shouldReturn(null);
    }
}
