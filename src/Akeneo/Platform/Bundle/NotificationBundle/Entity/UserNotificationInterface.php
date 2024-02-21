<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotification interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserNotificationInterface
{
    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set notification
     *
     * @param NotificationInterface $notification
     *
     * @return UserNotificationInterface
     */
    public function setNotification(NotificationInterface $notification);

    /**
     * Get notification
     *
     * @return NotificationInterface
     */
    public function getNotification();

    /**
     * Set user
     *
     * @param UserInterface $user
     *
     * @return UserNotificationInterface
     */
    public function setUser(UserInterface $user);

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser();

    /**
     * Set viewed
     *
     * @param bool $viewed
     *
     * @return UserNotificationInterface
     */
    public function setViewed($viewed);

    /**
     * Get viewed
     *
     * @return bool
     */
    public function isViewed();
}
