<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
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

    /**
     * @var string
     */
    protected $className;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $notificationRules;

    public function __construct(ObjectManager $em, $className)
    {
        $this->handlers = array();
        $this->em = $em;
        $this->className = $className;
        $this->notificationRules = $this->em->getRepository($this->className)
            ->getRules();
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
        $entity = $event->getEntity();

        // select rules by entity name and event name
        $notificationRules = $this->getRulesByCriteria(ClassUtils::getClass($entity), $event->getName());

        if (!$notificationRules->isEmpty()) {
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
     * Filter rules by criteria
     *
     * @param string $entityName
     * @param string $eventName
     * @return \Doctrine\Common\Collections\Collection|ArrayCollection
     */
    protected function getRulesByCriteria($entityName, $eventName)
    {
        return $this->notificationRules->filter(
            function (EmailNotification $item) use ($entityName, $eventName) {
                return $item->getEntityName() == $entityName
                    && $item->getEvent()->getName() == $eventName;
            }
        );
    }
}
