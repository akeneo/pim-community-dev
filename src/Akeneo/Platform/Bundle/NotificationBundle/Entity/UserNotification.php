<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotification implements UserNotificationInterface
{
    /** @var int */
    protected $id;

    /** @var bool */
    protected $viewed = false;

    /** @var NotificationInterface */
    protected $notification;

    /** @var UserInterface */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotification(NotificationInterface $notification): UserNotificationInterface
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotification(): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface
    {
        return $this->notification;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user): UserNotificationInterface
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): \Symfony\Component\Security\Core\User\UserInterface
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewed(bool $viewed): UserNotificationInterface
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isViewed(): bool
    {
        return $this->viewed;
    }
}
