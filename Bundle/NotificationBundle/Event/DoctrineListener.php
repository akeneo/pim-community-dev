<?php

namespace Oro\Bundle\NotificationBundle\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DoctrineListener implements ContainerAwareInterface
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
        $event = new DoctrineEvent(array('entity' => $args->getEntity()));
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
        $event = new DoctrineEvent(array('entity' => $args->getEntity()));
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
        $event = new DoctrineEvent(array('entity' => $args->getEntity()));
        $this->getEventDispatcher()->dispatch('oro.event.entity.post_remove', $event);
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getEventDispatcher()
    {
        return $this->container->get('event_dispatcher');
    }
}
