<?php
/**
 *
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Pim\Bundle\NotificationBundle\Manager;

use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * User notification manager
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NotificationManagerInterface
{
    /**
     * Send a user notification to given users
     *
     * @param array                 $users        Users which have to be notified
     * @param NotificationInterface $notification The notification to be sent
     *
     * @return NotificationManagerInterface
     */
    public function notify(array $users, NotificationInterface $notification);

    /**
     * Returns user notifications for the given user
     *
     * @param UserInterface $user
     * @param int           $offset
     * @param int           $limit
     *
     * @return UserNotificationInterface[]
     */
    public function getUserNotifications(UserInterface $user, $offset, $limit = 10);

    /**
     * Marks given user notifications as viewed
     *
     * @param UserInterface $user The user
     * @param int|null      $id   If null, all notifications will be marked as viewed
     */
    public function markAsViewed(UserInterface $user, $id);

    /**
     * Count unread notifications for the given user
     *
     * @param UserInterface $user
     *
     * @return int
     */
    public function countUnreadForUser(UserInterface $user);

    /**
     * Remove a notification
     *
     * @param UserInterface $user
     * @param int           $id
     */
    public function remove(UserInterface $user, $id);
}