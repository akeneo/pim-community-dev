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

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectStatusRepositoryInterface
{
    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     *
     * @return ProjectStatusInterface
     */
    public function findProjectStatus(ProjectInterface $project, UserInterface $user);

    /**
     * @param ProjectInterface $project
     * @param UserInterface    $user
     * @param bool             $isComplete
     */
    public function setProjectStatus(ProjectInterface $project, UserInterface $user, $isComplete);
}
