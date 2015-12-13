<?php

namespace Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Model\JobInstance;
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
     * @param string        $rawConfiguration
     *
     * @return JobExecution
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, $rawConfiguration = null);

    /**
     * Set config
     *
     * @param array $config
     *
     * @return JobLauncherInterface
     */
    public function setConfig(array $config);

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig();
}
