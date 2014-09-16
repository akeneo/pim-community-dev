<?php

namespace Pim\Bundle\NotificationBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactory;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;

/**
 * User notification manager
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotificationManager
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var EntityRepository */
    protected $repository;

    /** @var NotificationFactory  */
    protected $notificationFactory;

    /** @var UserNotificationFactory */
    protected $userNotificationFactory;

    /** @var UserManager */
    protected $userManager;

    /**
     * Construct
     *
     * @param EntityManager           $entityManager
     * @param EntityRepository        $repository
     * @param NotificationFactory     $notificationFactory
     * @param UserNotificationFactory $userNotificationFactory
     * @param UserManager             $userManager
     */
    public function __construct(
        EntityManager $entityManager,
        EntityRepository $repository,
        NotificationFactory $notificationFactory,
        UserNotificationFactory $userNotificationFactory,
        UserManager $userManager
    ) {
        $this->entityManager           = $entityManager;
        $this->repository              = $repository;
        $this->notificationFactory     = $notificationFactory;
        $this->userNotificationFactory = $userNotificationFactory;
        $this->userManager             = $userManager;
    }

    /**
     * Send a user notification to given users
     *
     * @param array  $users   Users which have to be notified
     *                        ['userName', ...] or [Oro\Bundle\UserBundle\Entity\User, ...]
     * @param string $message Message which has to be sent
     * @param string $type    success (default) | warning | error
     * @param array  $options ['route' => '', 'routeParams' => [], 'messageParams' => [], 'context => '']
     *
     * @return UserNotificationManager
     */
    public function notify(array $users, $message, $type = 'success', array $options = [])
    {
        $notification = $this->notificationFactory->createNotification($message, $type, $options);

        foreach ($users as $user) {
            $userEntity = is_object($user) ? $user : $this->userManager->findUserByUsername($user);
            $userNotification = $this->userNotificationFactory->createUserNotification($notification, $userEntity);
            $this->entityManager->persist($userNotification);
        }

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Returns user notifications for the given user
     *
     * @param User $user
     * @param int  $offset
     * @param int  $limit
     *
     * @return UserNotification[]
     */
    public function getUserNotifications(User $user, $offset, $limit = 10)
    {
        return $this->repository->findBy(['user' => $user], ['id' => 'DESC'], $limit, $offset);
    }

    /**
     * Marks given user notifications as viewed
     *
     * @param User           $user User
     * @param string|integer $id   Can be numeric or 'all'
     *
     * @return void
     */
    public function markAsViewed(User $user, $id)
    {
        // TODO: use a direct query in repository to mark notifications as viewed directly can be faster
        if (is_numeric($id)) {
            $findParams = ['user' => $user, 'id' => $id];
        } elseif ('all' === $id) {
            $findParams = ['user' => $user, 'viewed' => false];
        }
        $userNotifications = $this->repository->findBy($findParams);

        foreach ($userNotifications as $userNotification) {
            $userNotification->setViewed(true);
            $this->entityManager->persist($userNotification);
        }

        $this->entityManager->flush();
    }

    /**
     * Remove a notification
     *
     * @param User    $user
     * @param integer $id
     */
    public function remove(User $user, $id)
    {
        $notification = $this->repository->findOneBy(
            [
                'id' => $id,
                'user' => $user
            ]
        );

        if ($notification) {
            $this->entityManager->remove($notification);
            $this->entityManager->flush($notification);
        }
    }
}
