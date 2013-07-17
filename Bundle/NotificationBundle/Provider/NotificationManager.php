<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface;

class NotificationManager
{
    /**
     * @var EventHandlerInterface[] handlers
     */
    protected $handlers;

    /**
     * @var ObjectManager
     */
    protected $em;

    public function __construct(ObjectManager $em)
    {
        $this->handlers = array();
        $this->em = $em;
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
        $notificationRules = $this->em->getRepository($className)
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
}
