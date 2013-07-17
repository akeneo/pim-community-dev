<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
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
     * @param EmailNotification[] $matchedNotifications
     * @return mixed
     */
    public function handle(NotificationEvent $event, $matchedNotifications)
    {
        foreach ($matchedNotifications as $notification) {
            // compile email and add it to queue
        }
    }


}
