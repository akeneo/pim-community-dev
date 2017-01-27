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

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Checks if an user should be notified or not.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface NotificationCheckerInterface
{
    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     *
     * @return bool
     */
    public function isNotifiableForProjectCreation(ProjectInterface $project, UserInterface $user);

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     *
     * @return bool
     */
    public function isNotifiableForProjectFinished(ProjectInterface $project, UserInterface $user);
}
