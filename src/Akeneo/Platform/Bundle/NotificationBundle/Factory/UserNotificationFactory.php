<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Factory;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotification factory
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotificationFactory
{
    /** @var string */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * Creates a user notification
     *
     * @param NotificationInterface $notification
     * @param UserInterface         $user
     */
    public function createUserNotification(NotificationInterface $notification, UserInterface $user): UserNotificationInterface
    {
        $entity = new $this->className();

        $entity
            ->setNotification($notification)
            ->setUser($user);

        return $entity;
    }
}
