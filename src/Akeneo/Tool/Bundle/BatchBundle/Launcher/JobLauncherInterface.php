<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Launcher;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface of job launcher
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface JobLauncherInterface
{
    /**
     * Launch a job with command
     *
     * @param JobInstance   $jobInstance
     * @param UserInterface $user
     * @param array         $configuration
     *
     * @return JobExecution
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, array $configuration = []) : JobExecution;
}
