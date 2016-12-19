<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation;

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * It launches the calculation of a project depending of the user.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectCalculationJobLauncherInterface
{
    /**
     * @param UserInterface    $user
     * @param ProjectInterface $project
     */
    public function launch(UserInterface $user, ProjectInterface $project);
}
