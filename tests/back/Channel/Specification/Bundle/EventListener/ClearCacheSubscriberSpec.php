<?php

namespace Specification\Akeneo\Channel\Bundle\EventListener;

use Akeneo\Channel\Bundle\EventListener\ClearCacheSubscriber;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;

class ClearCacheSubscriberSpec extends ObjectBehavior
{
    function let(ChannelExistsWithLocaleInterface $cachedChannelExistsWithLocale)
    {
        $this->beConstructedWith($cachedChannelExistsWithLocale);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ClearCacheSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE => 'clearCache',
            StorageEvents::POST_SAVE_ALL => 'clearCache',
        ]);
    }

    function it_only_supports_locale_object(
        ChannelExistsWithLocaleInterface $cachedChannelExistsWithLocale,
        GenericEvent $event,
        \stdClass $object
    ) {
        $event->getSubject()->willReturn($object);
        $cachedChannelExistsWithLocale->clearCache()->shouldNotBeCalled();

        $this->clearCache($event);
    }

    function it_clears_locale_cache_on_save(
        ChannelExistsWithLocaleInterface $cachedChannelExistsWithLocale,
        ChannelInterface $channel,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($channel);

        $cachedChannelExistsWithLocale->clearCache()->shouldBeCalled();

        $this->clearCache($event);
    }
}
