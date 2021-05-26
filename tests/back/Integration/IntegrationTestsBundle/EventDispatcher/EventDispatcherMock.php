<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\EventDispatcher;

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
    public function dispatch($event)
    {
        $eventName = 1 < \func_num_args() ? func_get_arg(1) : null;

        if (\is_object($event)) {
            $eventName = $eventName ?? \get_class($event);
        } elseif (\is_string($event) && (null === $eventName || $eventName instanceof ContractsEvent || $eventName instanceof Event)) {
            @trigger_error(sprintf('Calling the "%s::dispatch()" method with the event name as the first argument is deprecated since Symfony 4.3, pass it as the second argument and provide the event object as the first argument instead.', EventDispatcherInterface::class), \E_USER_DEPRECATED);
            $swap = $event;
            $event = $eventName ?? new Event();
            $eventName = $swap;
        } else {
            throw new \TypeError(sprintf('Argument 1 passed to "%s::dispatch()" must be an object, "%s" given.', EventDispatcherInterface::class, \is_object($event) ? \get_class($event) : \gettype($event)));
        }
        $this->dispatchedEvents[$eventName] = $event;
        return $this->eventDispatcher->dispatch($event, $eventName);
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

    public function getEvent(string $eventClass): Event
    {
        return $this->dispatchedEvents[$eventClass];
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
        return $this->eventDispatcher->getListeners($eventName);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        return $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }
}
