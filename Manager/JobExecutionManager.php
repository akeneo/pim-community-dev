<?php

namespace Akeneo\Bundle\BatchBundle\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManager;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Akeneo\Bundle\BatchBundle\Job\ExitStatus;

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
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $jobExecutionClass;

    /**
     * @param EntityManager $entityManager
     * @param string        $jobExecutionClass
     */
    public function __construct(EntityManager $entityManager, $jobExecutionClass)
    {
        $this->entityManager     = $entityManager;
        $this->jobExecutionClass = $jobExecutionClass;
    }

    /**
     * CHeck if the given JoExecution is still running using his PID
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function checkRunningStatus(JobExecution $jobExecution)
    {
        if (ExitStatus::UNKNOWN === $jobExecution->getExitStatus()->getExitCode()) {
            return $this->processIsRunning($jobExecution);
        } else {
            return true;
        }
    }

    /**
     * Test if the process is still running
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    protected function processIsRunning(JobExecution $jobExecution)
    {
        if (($pid = intval($jobExecution->getPid())) > 0) {
            exec(sprintf('ps -p %s', $pid), $output, $returnCode);
        } else {
            throw new \InvalidArgumentException('The job execution PID is not valid');
        }

        return $returnCode === 0;
    }

    /**
     * Mark a job execution as failed
     * @param JobExecution $jobExecution
     */
    public function markAsFailed(JobExecution $jobExecution)
    {
        $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
        $jobExecution->setExitStatus(new ExitStatus(ExitStatus::FAILED));
        $jobExecution->setEndTime(new \DateTime('now'));
        $jobExecution->addFailureException(new \Exception('An exception occured during the job execution'));

        $this->getManagerForClass($this->jobExecutionClass)->persist($jobExecution);
        $this->getManagerForClass($this->jobExecutionClass)->flush();
    }
}
