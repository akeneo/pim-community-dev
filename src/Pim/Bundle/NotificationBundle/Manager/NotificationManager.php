<?php

namespace Pim\Bundle\NotificationBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactory;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
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
class NotificationManager
{
    /** @var EntityManager */
    protected $em;

    /** @var EntityRepository */
    protected $repository;

    /** @var NotificationFactory  */
    protected $notificationFactory;

    /** @var UserNotificationFactory */
    protected $userNotificationFactory;

    /** @var UserProviderInterface */
    protected $userProvider;

    /**
     * Construct
     *
     * @param EntityManager           $em
     * @param EntityRepository        $repository
     * @param NotificationFactory     $notificationFactory
     * @param UserNotificationFactory $userNotificationFactory
     * @param UserProviderInterface   $userProvider
     */
    public function __construct(
        EntityManager $em,
        EntityRepository $repository,
        NotificationFactory $notificationFactory,
        UserNotificationFactory $userNotificationFactory,
        UserProviderInterface $userProvider
    ) {
        $this->em                      = $em;
        $this->repository              = $repository;
        $this->notificationFactory     = $notificationFactory;
        $this->userNotificationFactory = $userNotificationFactory;
        $this->userProvider            = $userProvider;
    }

    /**
     * Send a user notification to given users
     *
     * @param array  $users   Users which have to be notified
     *                        ['userName', ...] or [UserInterface, ...]
     * @param string $message Message which has to be sent
     * @param string $type    success (default) | warning | error
     * @param array  $options ['route' => '', 'routeParams' => [], 'messageParams' => [], 'context => '']
     *
     * @return NotificationManager
     */
    public function notify(array $users, $message, $type = 'success', array $options = [])
    {
        $notification = $this->notificationFactory->createNotification($message, $type, $options);

        $userNotifications = [];

        foreach ($users as $user) {
            try {
                $user = is_object($user) ? $user : $this->userProvider->loadUserByUsername($user);
                $userNotifications[] = $this->userNotificationFactory->createUserNotification($notification, $user);
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
     * Returns user notifications for the given user
     *
     * @param UserInterface $user
     * @param integer       $offset
     * @param integer       $limit
     *
     * @return UserNotification[]
     */
    public function getUserNotifications(UserInterface $user, $offset, $limit = 10)
    {
        return $this->repository->findBy(['user' => $user], ['id' => 'DESC'], $limit, $offset);
    }

    /**
     * Marks given user notifications as viewed
     *
     * @param UserInterface $user The user
     * @param integer|null  $id   If null, all notifications will be marked as viewed
     */
    public function markAsViewed(UserInterface $user, $id)
    {
        $this->repository->markAsViewed($user, $id);
    }

    /**
     * Count unread notifications for the given user
     *
     * @param UserInterface $user
     *
     * @return integer
     */
    public function countUnreadForUser(UserInterface $user)
    {
        return $this->repository->countUnreadForUser($user);
    }

    /**
     * Remove a notification
     *
     * @param UserInterface $user
     * @param integer       $id
     */
    public function remove(UserInterface $user, $id)
    {
        $notification = $this->repository->findOneBy(
            [
                'id' => $id,
                'user' => $user
            ]
        );

        if ($notification) {
            $this->em->remove($notification);
            $this->em->flush($notification);
        }
    }
}
