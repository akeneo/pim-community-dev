<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotification factory
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotificationFactory implements UserNotificationFactoryInterface
{
    /** @var string */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function createUserNotification(NotificationInterface $notification, UserInterface $user)
    {
        $entity = new $this->className();

        $entity
            ->setNotification($notification)
            ->setUser($user);

        return $entity;
    }
}
