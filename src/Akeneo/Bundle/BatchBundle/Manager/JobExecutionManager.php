<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchBundle\Manager;

use Akeneo\Bundle\BatchBundle\Command\JobQueueConsumerCommand;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Model\JobExecution;
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
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Check if the given JoExecution is still running based on the last health check.
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function checkRunningStatus(JobExecution $jobExecution): bool
    {
        if (BatchStatus::STARTING !== $jobExecution->getStatus()->getValue() &&
            (ExitStatus::UNKNOWN === $jobExecution->getExitStatus()->getExitCode() ||
            ExitStatus::EXECUTING === $jobExecution->getExitStatus()->getExitCode())
        ) {
            return $this->processIsRunning($jobExecution);
        }

        return true;
    }

    /**
     * Test if the process is still running.
     *
     * @param JobExecution $jobExecution
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function processIsRunning(JobExecution $jobExecution): bool
    {
        $healthCheck = $jobExecution->getHealthcheckTime();

        if (null === $healthCheck) {
            return false;
        }

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $diffInSeconds = $now->getTimestamp() - $healthCheck->getTimestamp();

        return $diffInSeconds < JobQueueConsumerCommand::HEALTH_CHECK_INTERVAL + 10;
    }

    /**
     * Mark a job execution as failed
     *
     * @param JobExecution $jobExecution
     */
    public function markAsFailed(JobExecution $jobExecution): void
    {
        $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
        $jobExecution->setExitStatus(new ExitStatus(ExitStatus::FAILED));
        $jobExecution->setEndTime(new \DateTime('now'));
        $jobExecution->addFailureException(new \Exception('An exception occured during the job execution'));

        $this->entityManager->persist($jobExecution);
        $this->entityManager->flush();
    }
}
