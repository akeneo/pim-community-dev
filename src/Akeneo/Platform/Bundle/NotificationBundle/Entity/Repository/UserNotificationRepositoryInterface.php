<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotificationRepository interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserNotificationRepositoryInterface extends ObjectRepository
{
    /**
     * Returns the number of user notifications the user hasn't viewed
     *
     * @param UserInterface $user
     *
     * @return int
     */
    public function countUnreadForUser(UserInterface $user);

    /**
     * Marks user notifications as viewed
     *
     * @param UserInterface $user The user
     * @param int|null      $id   If null all notifications will be marked as viewed
     */
    public function markAsViewed(UserInterface $user, $id);
}
