<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Query\GetJobExecutionStatusInterface;

class JobStopper
{
    private JobRepositoryInterface $jobRepository;
    private GetJobExecutionStatusInterface $getJobExecutionStatus;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        GetJobExecutionStatusInterface $getJobExecutionStatus
    ) {
        $this->jobRepository = $jobRepository;
        $this->getJobExecutionStatus = $getJobExecutionStatus;
    }

    public function isStopping(StepExecution $stepExecution): bool
    {
        return BatchStatus::STOPPING === $this->getJobExecutionStatus->getByJobExecutionId(
            $stepExecution->getJobExecution()->getId()
        )->getValue();
    }

    public function stop(StepExecution $stepExecution): void
    {
        $stepExecution->setExitStatus(new ExitStatus(ExitStatus::STOPPED));
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
        $this->jobRepository->updateStepExecution($stepExecution);
    }
}
