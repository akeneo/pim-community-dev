<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionRequestsLimitReachedLog;
use Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber\EventsApiRequestsLimitEventSubscriber;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\GetDelayUntilNextRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\Sleep;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

class EventsApiRequestsLimitEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        Sleep $sleep,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith(
            $getDelayUntilNextRequest,
            10,
            $sleep,
            $logger
        );
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            WorkerRunningEvent::class => 'checkWebhookRequestLimit',
        ]);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldHaveType(EventsApiRequestsLimitEventSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_sleeps_until_the_delay_expire_when_limit_is_reached(
        GetDelayUntilNextRequest $getDelayUntilNextRequest,
        Sleep $sleep,
        LoggerInterface $logger
    ): void {
        $getDelayUntilNextRequest
            ->execute(Argument::type(\DateTimeImmutable::class), 10)
            ->willReturn(123);

        $logger->info(Argument::any())->shouldBeCalled();
        $sleep->sleep(123)->shouldBeCalled();

        $this->checkWebhookRequestLimit();
    }
}
