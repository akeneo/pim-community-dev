<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor;

class EmailNotificationHandler implements EventHandlerInterface
{
    /**
     * @var EmailNotificationProcessor
     */
    protected $processor;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EmailNotificationProcessor $processor
     * @param EntityManager              $em
     */
    public function __construct(EmailNotificationProcessor $processor, EntityManager $em)
    {
        $this->processor = $processor;
        $this->em        = $em;
    }

    /**
     * Handle event
     *
     * @param NotificationEvent   $event
     * @param EmailNotification[] $matchedNotifications
     * @return mixed
     */
    public function handle(NotificationEvent $event, $matchedNotifications)
    {
        $entity = $event->getEntity();

        // convert notification rules to a list of EmailNotificationInterface
        $notifications = array();
        foreach ($matchedNotifications as $notification) {
            $notifications[] = new EmailNotificationAdapter(
                $entity,
                $notification,
                $this->em
            );
        }

        // send notifications
        $this->processor->process($entity, $notifications);
    }
}
