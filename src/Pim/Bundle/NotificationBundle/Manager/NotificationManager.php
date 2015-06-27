<?php

namespace Pim\Bundle\NotificationBundle\Manager;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User notification manager
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationManager implements NotificationManagerInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var UserNotificationRepositoryInterface */
    protected $repository;

    /** @var NotificationFactoryInterface */
    protected $notificationFactory;

    /** @var UserNotificationFactoryInterface */
    protected $userNotifFactory;

    /** @var UserProviderInterface */
    protected $userProvider;

    /**
     * Construct
     *
     * @param EntityManager                       $em
     * @param UserNotificationRepositoryInterface $repository
     * @param NotificationFactoryInterface        $notificationFactory
     * @param UserNotificationFactoryInterface    $userNotifFactory
     * @param UserProviderInterface               $userProvider
     */
    public function __construct(
        EntityManager $em,
        UserNotificationRepositoryInterface $repository,
        NotificationFactoryInterface $notificationFactory,
        UserNotificationFactoryInterface $userNotifFactory,
        UserProviderInterface $userProvider
    ) {
        $this->em                  = $em;
        $this->repository          = $repository;
        $this->notificationFactory = $notificationFactory;
        $this->userNotifFactory    = $userNotifFactory;
        $this->userProvider        = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(array $users, $message, $type = 'success', array $options = [])
    {
        $notification = $this->notificationFactory->createNotification($message, $type, $options);

        $userNotifications = [];

        foreach ($users as $user) {
            try {
                $user = is_object($user) ? $user : $this->userProvider->loadUserByUsername($user);
                $userNotifications[] = $this->userNotifFactory->createUserNotification($notification, $user);
            } catch (UsernameNotFoundException $e) {
                continue;
            }
        }

        $this->em->persist($notification);
        foreach ($userNotifications as $userNotification) {
            $this->em->persist($userNotification);
        }
        $this->em->flush($notification);
        $this->em->flush($userNotifications);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserNotifications(UserInterface $user, $offset, $limit = 10)
    {
        return $this->repository->findBy(['user' => $user], ['id' => 'DESC'], $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function markAsViewed(UserInterface $user, $id)
    {
        $this->repository->markAsViewed($user, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function countUnreadForUser(UserInterface $user)
    {
        return $this->repository->countUnreadForUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(UserInterface $user, $id)
    {
        $notification = $this->repository->findOneBy(
            [
                'id'   => $id,
                'user' => $user
            ]
        );

        if ($notification) {
            $this->em->remove($notification);
            $this->em->flush($notification);
        }
    }
}
