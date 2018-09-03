<?php

namespace Akeneo\Platform\Bundle\NotificationBundle;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Notifier user
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Notifier implements NotifierInterface
{
    /** @var UserNotificationFactory */
    protected $userNotifFactory;

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var SaverInterface */
    protected $notificationSaver;

    /** @var BulkSaverInterface */
    protected $userNotifsSaver;

    /**
     * @param UserNotificationFactory $userNotifFactory
     * @param UserProviderInterface   $userProvider
     * @param SaverInterface          $notificationSaver
     * @param BulkSaverInterface      $userNotifsSaver
     */
    public function __construct(
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver
    ) {
        $this->userNotifFactory = $userNotifFactory;
        $this->userProvider = $userProvider;
        $this->notificationSaver = $notificationSaver;
        $this->userNotifsSaver = $userNotifsSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(NotificationInterface $notification, array $users)
    {
        $userNotifications = [];
        $users = $this->filterSystemUser($users);

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
     * Do not notify the System user.
     *
     * @param array $users
     *
     * @return array
     */
    private function filterSystemUser(array $users)
    {
        return array_filter(
            $users,
            function ($user) {
                if (is_string($user) && UserInterface::SYSTEM_USER_NAME === $user) {
                    return false;
                }
                if (is_object($user) && UserInterface::SYSTEM_USER_NAME === $user->getUsername()) {
                    return false;
                }

                return true;
            }
        );
    }
}
