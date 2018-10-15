<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EventDispatcherMock implements EventDispatcherInterface
{
    /** @var array */
    private $dispatchedEvents = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        $this->dispatchedEvents[$eventName] = $event;
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
        throw new NotImplementedException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        throw new NotImplementedException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, $listener)
    {
        throw new NotImplementedException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        throw new NotImplementedException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        throw new NotImplementedException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        throw new NotImplementedException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        throw new NotImplementedException('Method not implemented');
    }
}
