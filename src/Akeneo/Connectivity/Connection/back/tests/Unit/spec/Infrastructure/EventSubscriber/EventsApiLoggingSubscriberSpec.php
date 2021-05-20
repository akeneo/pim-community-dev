<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber\EventsApiLoggingSubscriber;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiLoggingSubscriberSpec extends ObjectBehavior
{
    public function let(EventsApiDebugRepository $repository): void
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

    public function it_flushs_the_logs($repository): void
    {
        $repository->flush()
            ->shouldBeCalled();

        $this->flushLogs();
    }
}
