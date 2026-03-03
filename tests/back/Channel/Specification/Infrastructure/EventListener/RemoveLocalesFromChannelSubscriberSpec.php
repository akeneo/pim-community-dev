<?php

namespace Specification\Akeneo\Channel\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\EventListener\RemoveLocalesFromChannelSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveLocalesFromChannelSubscriberSpec extends ObjectBehavior
{
    public function let(BulkSaverInterface $localeSaver): void
    {
        $this->beConstructedWith($localeSaver);
    }

    public function it_is_initializable(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(RemoveLocalesFromChannelSubscriber::class);
    }

    public function it_subscribes_to_remove_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_REMOVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    public function it_only_handles_channels(BulkSaverInterface $localeSaver): void
    {
        $product = new Product();

        $localeSaver->saveAll(Argument::any())->shouldNotBeCalled();

        $this->removeLocalesFromChannel(new GenericEvent($product));
        $this->saveLocales(new GenericEvent($product));
    }

    public function it_removes_locales_from_deleted_channel_and_saves_them(BulkSaverInterface $localeSaver): void
    {
        $enUS = new Locale();
        $frFR = new Locale();
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $channel->addLocale($enUS);
        $channel->addLocale($frFR);

        $localeSaver->saveAll([$enUS, $frFR])->shouldBeCalled();

        $this->removeLocalesFromChannel(new GenericEvent($channel));
        $this->saveLocales(new GenericEvent($channel));
    }
}
