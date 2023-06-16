<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Query\GetJobExecutionStatusInterface;

class JobStopper implements JobStopperInterface
{
    public function __construct(
        private readonly JobRepositoryInterface $jobRepository,
        private readonly GetJobExecutionStatusInterface $getJobExecutionStatus,
    ) {
    }

    public function isStopping(StepExecution $stepExecution): bool
    {
        return $this->getJobExecutionStatus->getByJobExecutionId($stepExecution->getJobExecution()->getId())->isStopping();
    }

    public function stop(StepExecution $stepExecution): void
    {
        $stepExecution->setExitStatus(new ExitStatus(ExitStatus::STOPPED));
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
        $this->jobRepository->updateStepExecution($stepExecution);
    }

    public function isPausing(StepExecution $stepExecution): bool
    {
        return $this->getJobExecutionStatus->getByJobExecutionId($stepExecution->getJobExecution()->getId())->isPausing();
    }

    public function pause(StepExecution $stepExecution, array $currentState): void
    {
        $stepExecution->setCurrentState($currentState);
        $stepExecution->setStatus(new BatchStatus(BatchStatus::PAUSED));
        $this->jobRepository->updateStepExecution($stepExecution);
    }
}
