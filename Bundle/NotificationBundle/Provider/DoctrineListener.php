<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\NotificationBundle\Event\NotificationEvent;

class DoctrineListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Post update event process
     *
     * @param LifecycleEventArgs $args
     * @return $this
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->getEventDispatcher()->dispatch('oro.notification,event.entity_post_update', $this->getNotificationEvent($args));

        return $this;
    }

    /**
     * Post persist event process
     *
     * @param LifecycleEventArgs $args
     * @return $this
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->getEventDispatcher()->dispatch('oro.notification,event.entity_post_persist', $this->getNotificationEvent($args));

        return $this;
    }

    /**
     * Post remove event process
     *
     * @param LifecycleEventArgs $args
     * @return $this
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->getEventDispatcher()->dispatch('oro.notification,event.entity_post_remove', $this->getNotificationEvent($args));

        return $this;
    }

    /**
     * Create new event instance
     *
     * @param LifecycleEventArgs $args
     * @return NotificationEvent
     */
    public function getNotificationEvent(LifecycleEventArgs $args)
    {
        $event = new NotificationEvent($args->getEntity());

        return $event;
    }

    /**
     * Getter for event dispatcher object
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Setter for event dispatcher object
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }
}
