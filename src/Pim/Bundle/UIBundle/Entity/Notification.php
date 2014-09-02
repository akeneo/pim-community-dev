<?php

namespace Pim\Bundle\UIBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Notification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Notification
{
    /** @var integer */
    protected $id;

    /** @var boolean */
    protected $viewed = false;

    /** @var NotificationEvent */
    protected $notificationEvent;

    /** @var User */
    protected $user;

    /**
     * @param NotificationEvent $event
     * @param User              $user
     */
    public function __construct(NotificationEvent $event, User $user)
    {
        $this->notificationEvent = $event;
        $this->user = $user;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set viewed
     *
     * @param boolean $viewed
     *
     * @return Notification
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * Get viewed
     *
     * @return boolean
     */
    public function isViewed()
    {
        return $this->viewed;
    }

    /**
     * Set notificationEvent
     *
     * @param NotificationEvent $notificationEvent
     *
     * @return Notification
     */
    public function setNotificationEvent(NotificationEvent $notificationEvent)
    {
        $this->notificationEvent = $notificationEvent;

        return $this;
    }

    /**
     * Get notificationEvent
     *
     * @return NotificationEvent
     */
    public function getNotificationEvent()
    {
        return $this->notificationEvent;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Notification
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
