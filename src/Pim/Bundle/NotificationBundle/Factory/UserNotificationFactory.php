<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Pim\Bundle\NotificationBundle\Entity\Notification;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;

/**
 * UserNotification factory
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotificationFactory
{
    /**
     * It creates a user notification
     *
     * @param Notification $notification
     * @param User         $user
     *
     * @return UserNotification
     */
    public function createUserNotification(Notification $notification, User $user)
    {
        return new UserNotification($notification, $user);
    }
}
