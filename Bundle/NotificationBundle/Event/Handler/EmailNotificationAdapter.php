<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationInterface;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;

/**
 * Adapts handler data to EmailNotificationInterface required for email notifications processor
 */
class EmailNotificationAdapter implements EmailNotificationInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EmailNotification
     */
    protected $notification;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * Constructor
     *
     * @param mixed             $entity
     * @param EmailNotification $notification
     * @param EntityManager     $em
     */
    public function __construct($entity, EmailNotification $notification, EntityManager $em)
    {
        $this->entity       = $entity;
        $this->notification = $notification;
        $this->em           = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->notification->getTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientEmails()
    {
        return $this->em->getRepository('Oro\Bundle\NotificationBundle\Entity\RecipientList')
            ->getRecipientEmails($this->notification->getRecipientList(), $this->entity);
    }
}
