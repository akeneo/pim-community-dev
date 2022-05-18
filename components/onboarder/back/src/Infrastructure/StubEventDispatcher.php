<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure;

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

    public function addListener(string $eventName, callable $listener, int $priority = 0)
    {
        // TODO: Implement addListener() method.
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        // TODO: Implement addSubscriber() method.
    }

    public function removeListener(string $eventName, callable $listener)
    {
        // TODO: Implement removeListener() method.
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        // TODO: Implement removeSubscriber() method.
    }

    public function getListeners(string $eventName = null)
    {
        // TODO: Implement getListeners() method.
    }

    public function getListenerPriority(string $eventName, callable $listener)
    {
        // TODO: Implement getListenerPriority() method.
    }

    public function hasListeners(string $eventName = null)
    {
        // TODO: Implement hasListeners() method.
    }
}
