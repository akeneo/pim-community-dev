<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers\EventsApiClearCacheSubscriber;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiClearCacheSubscriberSpec extends ObjectBehavior
{
    public function let(CacheClearerInterface $cacheClearer): void
    {
        $this->beConstructedWith($cacheClearer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EventsApiClearCacheSubscriber::class);
    }

    public function it_subscribes_to_message_processed_event(): void
    {
        $this->getSubscribedEvents()
            ->shouldReturn([MessageProcessedEvent::class => 'clearCache']);
    }

    public function it_clears_the_cache($cacheClearer): void
    {
        $cacheClearer->clear()
            ->shouldBeCalled();

        $this->clearCache();
    }
}
