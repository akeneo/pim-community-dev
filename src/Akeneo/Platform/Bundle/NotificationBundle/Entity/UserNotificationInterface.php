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
     */
    public function getId(): int;

    /**
     * Set notification
     *
     * @param NotificationInterface $notification
     */
    public function setNotification(NotificationInterface $notification): \Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotificationInterface;

    /**
     * Get notification
     */
    public function getNotification(): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Set user
     *
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user): \Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotificationInterface;

    /**
     * Get user
     */
    public function getUser(): \Symfony\Component\Security\Core\User\UserInterface;

    /**
     * Set viewed
     *
     * @param bool $viewed
     */
    public function setViewed(bool $viewed): \Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotificationInterface;

    /**
     * Get viewed
     */
    public function isViewed(): bool;
}
