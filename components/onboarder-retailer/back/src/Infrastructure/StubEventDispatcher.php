<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StubEventDispatcher implements EventDispatcherInterface
{
    public array $dispatchedEvents = [];

    public function dispatch(object $event, string $eventName = null): object
    {
        $this->dispatchedEvents[] = $event;

        return $event;
    }

    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        // TODO: Implement addListener() method.
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        // TODO: Implement addSubscriber() method.
    }

    public function removeListener(string $eventName, callable $listener): void
    {
        // TODO: Implement removeListener() method.
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        // TODO: Implement removeSubscriber() method.
    }

    public function getListeners(string $eventName = null): array
    {
        return [];
    }

    public function getListenerPriority(string $eventName, callable $listener): int
    {
        return 0;
    }

    public function hasListeners(string $eventName = null): bool
    {
        return false;
    }
}
