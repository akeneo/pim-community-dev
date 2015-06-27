<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * UserNotification factory interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserNotificationFactoryInterface
{
    /**
     * Creates a user notification
     *
     * @param NotificationInterface $notification
     * @param UserInterface         $user
     *
     * @return UserNotificationInterface
     */
    public function createUserNotification(NotificationInterface $notification, UserInterface $user);
}