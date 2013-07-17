<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Symfony\Component\EventDispatcher\Event;

class NotificationManager
{
    /**
     * @var EventHandlerInterface[] handlers
     */
    protected $handlers;

    public function __construct()
    {
        $this->handlers = array();
    }

    /**
     * Add handler to list
     *
     * @param EventHandlerInterface $handler
     */
    public function addHandler(EventHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Process events with handlers
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     * @return Event
     */
    public function process(Event $event)
    {
        $className = 'Oro\Bundle\NotificationBundle\Entity\EmailNotification';
        $entity = $event->getEntity();

        // select rules by entity name and event name
        $em = $this->getEntityManager($event);
        $notificationRules = $em->getRepository($className)
            ->getRulesByCriteria(get_class($entity), $event->getName());

        if (!empty($rules)) {
            /** @var EventHandlerInterface $handler */
            foreach ($this->handlers as $handler) {
                $handler->handle($event, $notificationRules);

                if ($event->isPropagationStopped()) {
                    break;
                }
            }
        }


        return $event;
    }

    /**
     * Return list of handlers
     *
     * @return EventHandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Get entity manager
     *
     * @param NotificationEvent $event
     * @return ObjectManager
     */
    public function getEntityManager(NotificationEvent $event)
    {
        return $event->getEntityManager() ?: $this->em;
    }
}
