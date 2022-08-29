<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Manager;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\ORM\EntityManager;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobExecutionManager
{
    public function __construct(protected EntityManager $entityManager)
    {
    }

    /**
     * Check if the given JobExecution is still running using his PID
     */
    public function checkRunningStatus(JobExecution $jobExecution): bool
    {
        if (BatchStatus::STARTING !== $jobExecution->getStatus()->getValue() &&
            $jobExecution->getExitStatus()->isRunning()
        ) {
            return $this->processIsRunning($jobExecution);
        }

        return true;
    }

    /**
     * Test if the process is still running
     */
    protected function processIsRunning(JobExecution $jobExecution): bool
    {
        $pid = intval($jobExecution->getPid());

        if ($pid <= 0) {
            throw new \InvalidArgumentException('The job execution PID is not valid');
        }

        exec(sprintf('ps -p %s', $pid), $output, $returnCode);

        return 0 === $returnCode;
    }

    /**
     * Mark a job execution as failed
     */
    public function markAsFailed(JobExecution $jobExecution): void
    {
        $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
        $jobExecution->setExitStatus(new ExitStatus(ExitStatus::FAILED));
        $jobExecution->setEndTime(new \DateTime('now'));
        $jobExecution->addFailureException(new \Exception('An exception occurred during the job execution'));

        $this->entityManager->persist($jobExecution);
        $this->entityManager->flush();
    }
}
