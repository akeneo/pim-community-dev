<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers\EventsApiLoggingSubscriber;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiLoggingSubscriberSpec extends ObjectBehavior
{
    public function let(EventsApiDebugRepositoryInterface $repository): void
    {
        $this->beConstructedWith($repository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EventsApiLoggingSubscriber::class);
    }

    public function it_subscribes_to_message_processed_event(): void
    {
        $this->getSubscribedEvents()
            ->shouldReturn([MessageProcessedEvent::class => 'flushLogs']);
    }

    public function it_flushes_the_logs($repository): void
    {
        $repository->flush()
            ->shouldBeCalled();

        $this->flushLogs();
    }
}
