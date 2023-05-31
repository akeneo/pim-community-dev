<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\JobExecution;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Execute a JobExecution with the provided ID
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
class ExecuteJobExecutionHandler implements ExecuteJobExecutionHandlerInterface
{
    public function __construct(
        private BatchLogHandler        $batchLogHandler,
        private JobRepositoryInterface $jobRepository,
        private JobRegistry            $jobRegistry,
        private FeatureFlags           $featureFlags,
    ) {
    }

    public function executeFromJobExecutionId(int $executionId): JobExecution
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->getJobManager()->getRepository(JobExecution::class)->find($executionId);

        if (!$jobExecution) {
            throw new \InvalidArgumentException(sprintf('Could not find job execution "%s".', $executionId));
        }

        $this->doExecute($jobExecution);

        return $jobExecution;
    }

    private function doExecute(JobExecution $jobExecution): void
    {
        if (!$this->canBeExecuted($jobExecution)) {
            throw new \RuntimeException(
                sprintf('Job execution "%s" has invalid status: %s', $jobExecution->getId(), $jobExecution->getStatus())
            );
        }
        if (null === $jobExecution->getExecutionContext()) {
            $jobExecution->setExecutionContext(new ExecutionContext());
        }

        $jobInstance = $jobExecution->getJobInstance();
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobExecution->setPid(getmypid());
        $job->getJobRepository()->updateJobExecution($jobExecution);

        $this->batchLogHandler->setSubDirectory($jobExecution->getId());

        $job->execute($jobExecution);
        $job->getJobRepository()->updateJobExecution($jobExecution);
    }

    private function getJobManager(): EntityManagerInterface
    {
        return $this->jobRepository->getJobManager();
    }

    private function canBeExecuted(JobExecution $jobExecution): bool
    {
        return $jobExecution->getStatus()->isStarting()
            || $jobExecution->getStatus()->isStopping()
            || ($jobExecution->getStatus()->isPaused() && $this->featureFlags->isEnabled('pause_jobs'));
    }
}
