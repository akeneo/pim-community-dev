<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notify the user when the project due date is close.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectDueDateReminderNotifierInterface
{
    /**
     * Notify the user when the project due date is close.
     *
     * @param UserInterface          $user
     * @param ProjectInterface       $project
     * @param ProjectCompleteness    $projectCompleteness
     *
     * @return bool
     */
    public function notifyUser(
        UserInterface $user,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness
    );
}
