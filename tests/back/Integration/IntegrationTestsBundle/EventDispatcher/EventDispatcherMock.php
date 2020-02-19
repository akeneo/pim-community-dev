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
    public function dispatch($eventName)
    {
        /**
         * With SF 4.3, eventName is deleted (but still work for BC.
         * If this method is called with 2 parameters (event, string), it implies than we try to dispatch an
         * event with the old way. So we have to switch the arguments to be dispatched correctly.
         * The right way to fix this should be to use Symfony\Contracts\EventDispatcher\EventDispatcherInterface
         * and switch every dispatch calls arguments.
         * @see https://symfony.com/blog/new-in-symfony-4-3-simpler-event-dispatching
         */
        if (2 > count(func_get_args()) && is_object(func_get_arg(0))) {
            $event = func_get_arg(0);
            $eventName = null;
        } elseif (func_get_arg(1) && is_string(func_get_arg(1))) {
            $event = func_get_arg(0);
            $eventName = func_get_arg(1);
        } else {
            $event = func_get_arg(1);
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
