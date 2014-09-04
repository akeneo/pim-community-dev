<?php

namespace Pim\Bundle\NotificationBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * UserNotification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotification
{
    /** @var integer */
    protected $id;

    /** @var boolean */
    protected $viewed = false;

    /** @var NotificationEvent */
    protected $event;

    /** @var User */
    protected $user;

    /**
     * @param NotificationEvent $event
     * @param User              $user
     */
    public function __construct(NotificationEvent $event, User $user)
    {
        $this->event = $event;
        $this->user  = $user;
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
     * @return UserNotification
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
     * Set event
     *
     * @param NotificationEvent $event
     *
     * @return UserNotification
     */
    public function setEvent(NotificationEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return NotificationEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return UserNotification
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
