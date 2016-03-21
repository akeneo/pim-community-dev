<?php

namespace Pim\Bundle\NotificationBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Pim\Bundle\NotificationBundle\Notifier;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User notification manager
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be remove in 1.7. Please look at each method to know the replacement
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

    /** @var NotifierInterface */
    protected $notifier;

    /**
     * @param UserNotificationRepositoryInterface $userNotifRepository
     * @param UserNotificationFactory             $userNotifFactory
     * @param UserProviderInterface               $userProvider
     * @param SaverInterface                      $notificationSaver
     * @param BulkSaverInterface                  $userNotifsSaver
     * @param RemoverInterface                    $userNotifRemover
     * @param NotifierInterface                   $notifier
     */
    public function __construct(
        UserNotificationRepositoryInterface $userNotifRepository,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        RemoverInterface $userNotifRemover,
        NotifierInterface $notifier
    ) {
        $this->userNotifRepository = $userNotifRepository;
        $this->userNotifFactory    = $userNotifFactory;
        $this->userProvider        = $userProvider;
        $this->notificationSaver   = $notificationSaver;
        $this->userNotifsSaver     = $userNotifsSaver;
        $this->userNotifRemover    = $userNotifRemover;
        $this->notifier            = $notifier;
    }

    /**
     * Send a user notification to given users
     *
     * @param NotificationInterface    $notification
     * @param string[]|UserInterface[] $users        Users which have to be notified
     *
     * @return NotificationManager
     *
     * @deprecated will be removed in 1.7. Please use Pim\Bundle\NotificationBundle\Notifier::notify()
     */
    public function notify(NotificationInterface $notification, array $users)
    {
        $this->notifier->notify($notification, $users);

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
     *
     * @deprecated will be removed in 1.7. Please use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository::findBy()
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
     *
     * @deprecated will be removed in 1.7. Please use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository::markAsViewed()
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
     *
     * @deprecated will be removed in 1.7. Please use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository::countUnreadForUser()
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
     *
     * @deprecated will be removed in 1.7. Please use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemove::remove()
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
