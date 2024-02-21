<?php

namespace Specification\Akeneo\Channel\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\EventListener\CurrencyDisablingSubscriber;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Exception\LinkedChannelException;
use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CurrencyDisablingSubscriberSpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository)
    {
        $this->beConstructedWith($channelRepository);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldImplement(CurrencyDisablingSubscriber::class);
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
