<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\LinkedChannelException;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CurrencyDisablingSubscriberSpec extends ObjectBehavior
{
    public function let(ChannelRepositoryInterface $channelRepository)
    {
        $this->beConstructedWith($channelRepository);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\EventSubscriber\CurrencyDisablingSubscriber');
    }

    public function it_does_not_throw_when_this_is_not_a_currency(
        GenericEvent $event,
        \StdClass $notACurrency
    ) {
        $event->getSubject()->willReturn($notACurrency);

        $this
            ->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
            ->during('checkChannelLink', [$event]);
    }

    public function it_does_not_throw_when_currency_is_not_saved(
        GenericEvent $event,
        CurrencyInterface $currency
    ) {
        $event->getSubject()->willReturn($currency);
        $currency->getId()->willReturn(null);

        $this
            ->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
            ->during('checkChannelLink', [$event]);
    }

    public function it_does_not_throw_when_currency_is_activated(
        GenericEvent $event,
        CurrencyInterface $currency
    ) {
        $event->getSubject()->willReturn($currency);
        $currency->getId()->willReturn(42);
        $currency->isActivated()->willReturn(true);

        $this
            ->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
            ->during('checkChannelLink', [$event]);
    }

    public function it_does_not_throw_when_currency_is_unused(
        $channelRepository,
        GenericEvent $event,
        CurrencyInterface $currency
    ) {
        $event->getSubject()->willReturn($currency);
        $currency->getId()->willReturn(42);
        $currency->isActivated()->willReturn(false);
        $channelRepository->getChannelCountUsingCurrency($currency)->willReturn(0);

        $this
            ->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
            ->during('checkChannelLink', [$event]);
    }

    public function it_throws_linked_channel_exception(
        $channelRepository,
        GenericEvent $event,
        CurrencyInterface $currency
    ) {
        $event->getSubject()->willReturn($currency);
        $currency->getId()->willReturn(42);
        $currency->isActivated()->willReturn(false);
        $channelRepository->getChannelCountUsingCurrency($currency)->willReturn(1);

        $this
            ->shouldThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
            ->during('checkChannelLink', [$event]);
    }
}
