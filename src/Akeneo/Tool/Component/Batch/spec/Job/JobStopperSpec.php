<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Query\GetJobExecutionStatusInterface;
use PhpSpec\ObjectBehavior;

class JobStopperSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        GetJobExecutionStatusInterface $getJobExecutionStatus
    ) {
        $this->beConstructedWith(
            $jobRepository,
            $getJobExecutionStatus
        );
    }

    function it_tells_if_a_job_is_stopping(
        $getJobExecutionStatus,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        BatchStatus $batchStatus
    ) {
        $jobExecution->getId()->willReturn(3);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $batchStatus->isStopping()->willReturn(true);
        $getJobExecutionStatus->getByJobExecutionId(3)->willReturn($batchStatus);

        $this->isStopping($stepExecution)->shouldReturn(true);
    }

    function it_tells_if_a_job_is_not_stopping(
        $getJobExecutionStatus,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        BatchStatus $batchStatus
    ) {
        $jobExecution->getId()->willReturn(5);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $batchStatus->isStopping()->willReturn(false);
        $getJobExecutionStatus->getByJobExecutionId(5)->willReturn($batchStatus);

        $this->isStopping($stepExecution)->shouldReturn(false);
    }

    function it_stops_a_job(StepExecution $stepExecution)
    {
        $stepExecution->setExitStatus(new ExitStatus(ExitStatus::STOPPED))->shouldBeCalled();
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPED))->shouldBeCalled();

        $this->stop($stepExecution);
    }

    function it_tells_if_a_job_is_pausing(
        $getJobExecutionStatus,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        BatchStatus $batchStatus
    ) {
        $jobExecution->getId()->willReturn(3);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $batchStatus->isPausing()->willReturn(true);
        $getJobExecutionStatus->getByJobExecutionId(3)->willReturn($batchStatus);

        $this->isPausing($stepExecution)->shouldReturn(true);
    }

    function it_pauses_a_job(StepExecution $stepExecution)
    {
        $stepExecution->setStatus(new BatchStatus(BatchStatus::PAUSED))->shouldBeCalled();
        $stepExecution->setCurrentState(["position" => 1])->shouldBeCalled();

        $this->pause($stepExecution, ["position" => 1]);

    }

}
