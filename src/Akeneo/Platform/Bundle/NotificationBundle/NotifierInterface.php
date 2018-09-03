<?php

namespace Akeneo\Platform\Bundle\NotificationBundle;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notifier interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NotifierInterface
{
    /**
     * Send a user notification to given users
     *
     * @param NotificationInterface    $notification
     * @param string[]|UserInterface[] $users
     *
     * @return NotifierInterface
     */
    public function notify(NotificationInterface $notification, array $users);
}
