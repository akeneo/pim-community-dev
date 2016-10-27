<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Job\Launcher;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectLauncherInterface
{
    /**
     * @param UserInterface    $user
     * @param ProjectInterface $project
     */
    public function launch(UserInterface $user, ProjectInterface $project);
}
