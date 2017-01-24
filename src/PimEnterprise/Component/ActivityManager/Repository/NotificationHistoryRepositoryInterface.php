<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Repository;

use PimEnterprise\Component\ActivityManager\Model\NotificationHistoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface NotificationHistoryRepositoryInterface
{

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     *
     * @return NotificationHistoryInterface
     */
    public function findNotificationHistory($project, $user);

    /**
     * @param $project
     * @param $user
     *
     * @return bool
     */
    public function hasBeenNotifiedForProjectCreation($project, $user);

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     *
     * @return bool
     */
    public function hasBeenNotifiedForProjectFinished($project, $user);
}
