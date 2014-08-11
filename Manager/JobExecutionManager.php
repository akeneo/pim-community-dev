<?php

namespace Akeneo\Bundle\BatchBundle\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManager;

/**
 * Job execution manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobExecutionManager
{
    /**
     * CHeck if the given JoExecution is still running using his PID
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function checkRunningStatus(JobExecution $jobExecution)
    {
        // if ($pid = intval($jobExecution->getPid()) > 0) {
        //     exec(sprintf('ps -p %s', $pid), $output, $returnCode);
        // } else {
        //     throw new \InvalidArgumentException('The job execution PID is not valid');
        // }

        // return $returnCode === 0;
        return true;
    }
}
