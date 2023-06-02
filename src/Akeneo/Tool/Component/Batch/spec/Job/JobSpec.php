<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
//use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;


class JobSpec extends ObjectBehavior
{
    function let(
        JobExecution $jobExecution,
        ExecutionContext $executionContext,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $eventDispatcher->dispatch(Argument::any(), Argument::any())->willReturn(new \stdClass());

        $jobExecution->setStartTime(Argument::any())->shouldBeCalled();
        $jobExecution->setEndTime(Argument::any())->shouldBeCalled();

    }
    function it_executes_a_job_execution(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface   $jobRepository,
        JobExecution             $jobExecution,
        StepInterface            $step1,
        StepInterface            $step2,
        BatchStatus              $batchStatusStarting,
        BatchStatus              $batchStatusCompleted,
        StepExecution            $stepExecution1,
        StepExecution            $stepExecution2,
        ExitStatus               $exitStatus,
    ) {
        $steps = [
            $step1,
            $step2,
        ];

        $batchStatusStarting->getValue()->willReturn(BatchStatus::STARTING);
        $jobExecution->getStatus()->willReturn($batchStatusStarting);

        $jobExecution->setStatus(Argument::that(static fn (BatchStatus $arg) => $arg->getValue() === BatchStatus::STARTED))->shouldBeCalled();

        $this->initializeStep('step_1', $step1, $stepExecution1, $batchStatusCompleted, $exitStatus, $jobExecution);
        $this->initializeStep('step_2', $step2, $stepExecution2, $batchStatusCompleted, $exitStatus, $jobExecution);

        $batchStatusCompleted->getValue()->willReturn(BatchStatus::COMPLETED);

        $callNb = 0;

        $jobExecution->getStepExecutions()->will(function () use (&$callNb, $stepExecution1, $stepExecution2) {
            if ($callNb<2) {
                $callNb++;
                if ($callNb === 2) {
                    return [$stepExecution1];
                }
                return [];
            }
            return [$stepExecution1, $stepExecution2];
        });

        $jobExecution->isStopping()->willReturn(false);

        $jobExecution->upgradeStatus(BatchStatus::COMPLETED)->shouldBeCalled();
        $jobExecution->setExitStatus($exitStatus)->shouldBeCalled();

        $this->beConstructedWith("a_starting_job", $eventDispatcher, $jobRepository, $steps);

        $this->execute($jobExecution);
    }

    private function initializeStep(string $stepName, StepInterface $step, StepExecution $stepExecution, BatchStatus $batchStatus, ExitStatus $exitStatus, JobExecution $jobExecution)
    {
        $step->getName()->willReturn($stepName);

        $jobExecution->createStepExecution($stepName)->shouldBeCalled()->willReturn($stepExecution);

        $stepExecution->getStatus()->willReturn($batchStatus);
        $stepExecution->getExitStatus()->willReturn($exitStatus);

        $step->execute($stepExecution)->shouldBeCalled();
    }
}
