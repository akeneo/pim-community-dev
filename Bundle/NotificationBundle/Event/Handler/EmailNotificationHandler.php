<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;

class EmailNotificationHandler implements EventHandlerInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * Handle event
     *
     * @param NotificationEvent $event
     * @return mixed
     */
    public function handle(NotificationEvent $event)
    {
        $className = 'Oro\Bundle\NotificationBundle\Entity\EmailNotification';
        $entity = $event->getEntity();

        // select rules by entity name and event name
        $em = $this->getEntityManager($event);
        $rules = $em->getRepository($className)->findBy(
            array(
                'entity_name' => get_class($entity),
                'event.name'  => $event->getName(),
            )
        );

        die();
    }

    public function getEntityManager(NotificationEvent $event)
    {
        return $event->getEntityManager() ?: $this->em;
    }
}
