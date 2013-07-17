<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface;

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
     * @param NotificationEvent $event
     * @return NotificationEvent
     */
    public function process(NotificationEvent $event)
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

    public function getEntityManager(NotificationEvent $event)
    {
        return $event->getEntityManager() ?: $this->em;
    }
}
