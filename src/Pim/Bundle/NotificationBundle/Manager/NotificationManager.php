<?php

namespace Pim\Bundle\NotificationBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
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
    /** @var UserNotificationRepositoryInterface */
    protected $userNotifRepository;

    /** @var UserNotificationFactory */
    protected $userNotifFactory;

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var SaverInterface  */
    protected $notificationSaver;

    /** @var BulkSaverInterface */
    protected $userNotifsSaver;

    /** @var RemoverInterface */
    protected $userNotifRemover;

    /**
     * @param UserNotificationRepositoryInterface $userNotifRepository
     * @param UserNotificationFactory             $userNotifFactory
     * @param UserProviderInterface               $userProvider
     * @param SaverInterface                      $notificationSaver
     * @param BulkSaverInterface                  $userNotifsSaver
     * @param RemoverInterface                    $userNotifRemover
     */
    public function __construct(
        UserNotificationRepositoryInterface $userNotifRepository,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        RemoverInterface $userNotifRemover
    ) {
        $this->userNotifRepository = $userNotifRepository;
        $this->userNotifFactory    = $userNotifFactory;
        $this->userProvider        = $userProvider;
        $this->notificationSaver   = $notificationSaver;
        $this->userNotifsSaver     = $userNotifsSaver;
        $this->userNotifRemover    = $userNotifRemover;
    }

    /**
     * Send a user notification to given users
     *
     * @param array                 $users Users which have to be notified
     *                                     [(string) 'userName', ...] or UserInterface[]
     * @param NotificationInterface $notification
     *
     * @return NotificationManager
     */
    public function notify(array $users, NotificationInterface $notification)
    {
        $userNotifications = [];
        foreach ($users as $user) {
            try {
                $user = is_object($user) ? $user : $this->userProvider->loadUserByUsername($user);
                $userNotifications[] = $this->userNotifFactory->createUserNotification($notification, $user);
            } catch (UsernameNotFoundException $e) {
                continue;
            }
        }

        $this->notificationSaver->save($notification);
        $this->userNotifsSaver->saveAll($userNotifications);

        return $this;
    }

    /**
     * Returns user notifications for the given user
     *
     * @param UserInterface $user
     * @param int           $offset
     * @param int           $limit
     *
     * @return UserNotificationInterface[]
     */
    public function getUserNotifications(UserInterface $user, $offset, $limit = 10)
    {
        return $this->userNotifRepository->findBy(['user' => $user], ['id' => 'DESC'], $limit, $offset);
    }

    /**
     * Marks given user notifications as viewed
     *
     * @param UserInterface $user The user
     * @param int|null      $id   If null, all notifications will be marked as viewed
     */
    public function markAsViewed(UserInterface $user, $id)
    {
        $this->userNotifRepository->markAsViewed($user, $id);
    }

    /**
     * Count unread notifications for the given user
     *
     * @param UserInterface $user
     *
     * @return int
     */
    public function countUnreadForUser(UserInterface $user)
    {
        return $this->userNotifRepository->countUnreadForUser($user);
    }

    /**
     * Remove a notification
     *
     * @param UserInterface $user
     * @param int           $id
     */
    public function remove(UserInterface $user, $id)
    {
        $notification = $this->userNotifRepository->findOneBy(
            [
                'id'   => $id,
                'user' => $user
            ]
        );

        if ($notification) {
            $this->userNotifRemover->remove($notification);
        }
    }
}
