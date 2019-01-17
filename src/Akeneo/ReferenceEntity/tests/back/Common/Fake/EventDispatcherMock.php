<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EventDispatcherMock implements EventDispatcherInterface
{
    /** @var array */
    private $dispatchedEvents = [];

    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        $this->dispatchedEvents[$eventName] = $event;
        $this->eventDispatcher->dispatch($eventName, $event);
    }

    public function assertEventDispatched(string $expectedEventClass): void
    {
        Assert::assertArrayHasKey(
            $expectedEventClass,
            $this->dispatchedEvents,
            sprintf('Expected event of type "%s" to be dispatched, but it was not found.', $expectedEventClass)
        );
    }

    public function assertNoEventDispatched(): void
    {
        Assert::assertCount(
            0,
            $this->dispatchedEvents,
            sprintf('Expected to have no dispatched event, but some were found: %s', implode(', ', array_keys($this->dispatchedEvents)))
        );
    }

    public function reset(): void
    {
        $this->dispatchedEvents = [];
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, $listener)
    {
        $this->eventDispatcher->removeListener($eventName, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->eventDispatcher->removeSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        $this->eventDispatcher->getListeners($eventName);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        $this->eventDispatcher->hasListeners($eventName);
    }
}
