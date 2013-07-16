<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Post update event process
     *
     * @param LifecycleEventArgs $args
     * @return $this
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $event = new NotificationEvent(array('entity' => $args->getEntity()));
        $this->getEventDispatcher()->dispatch('oro.event.entity.post_update', $event);

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
        $event = new NotificationEvent(array('entity' => $args->getEntity()));
        $this->getEventDispatcher()->dispatch('oro.event.entity.post_persist', $event);

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
        $event = new NotificationEvent(array('entity' => $args->getEntity()));
        $this->getEventDispatcher()->dispatch('oro.event.entity.post_remove', $event);

        return $this;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
