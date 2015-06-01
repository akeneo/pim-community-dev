<?php

namespace Pim\Bundle\NotificationBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotification
{
    /** @var int */
    protected $id;

    /** @var bool */
    protected $viewed = false;

    /** @var Notification */
    protected $notification;

    /** @var UserInterface */
    protected $user;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set notification
     *
     * @param Notification $notification
     *
     * @return UserNotification
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     *
     * @return UserNotification
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set viewed
     *
     * @param bool $viewed
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
     * @return bool
     */
    public function isViewed()
    {
        return $this->viewed;
    }
}
