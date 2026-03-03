<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Query\GetJobExecutionStatusInterface;
use Psr\Log\LoggerInterface;

class JobStopper implements JobStopperInterface
{
    public function __construct(
        private readonly JobRepositoryInterface $jobRepository,
        private readonly GetJobExecutionStatusInterface $getJobExecutionStatus,
        private readonly LoggerInterface $logger,
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
        $stepExecution->setCurrentState([...$stepExecution->getCurrentState(), ...$currentState]);
        $stepExecution->setStatus(new BatchStatus(BatchStatus::PAUSED));
        $this->jobRepository->updateStepExecution($stepExecution);

        $this->logger->notice('Job has been paused.', [
            'job_execution_id' => $stepExecution->getJobExecution()->getId(),
            'job_code' => $stepExecution->getJobExecution()->getJobInstance()->getCode(),
            'step_execution_id' => $stepExecution->getId(),
            'step_name' => $stepExecution->getStepName(),
            'current_state' => $stepExecution->getCurrentState(),
        ]);
    }
}
