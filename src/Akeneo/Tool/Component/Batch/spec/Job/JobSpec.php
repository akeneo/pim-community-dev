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

class JobSpec extends ObjectBehavior
{
    function let(
        JobExecution $jobExecution,
        ExecutionContext $executionContext,
        EventDispatcherInterface $eventDispatcher,
        BatchStatus $batchStatusStarting,
        BatchStatus $batchStatusCompleted,
        BatchStatus $batchStatusPaused,
        BatchStatus $batchStatusFailed,
    ) {
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $eventDispatcher->dispatch(Argument::any(), Argument::any())->willReturn(new \stdClass());

        $jobExecution->setStartTime(Argument::any())->shouldBeCalled();

        $batchStatusStarting->getValue()->willReturn(BatchStatus::STARTING);
        $batchStatusCompleted->getValue()->willReturn(BatchStatus::COMPLETED);
        $batchStatusPaused->getValue()->willReturn(BatchStatus::PAUSED);
        $batchStatusFailed->getValue()->willReturn(BatchStatus::FAILED);

        $this->assertStatusShouldBeSet($jobExecution, BatchStatus::STARTED);

        $jobExecution->isStopping()->willReturn(false);
        $jobExecution->setExitStatus(Argument::any())->shouldBeCalled();
    }

    function it_executes_a_starting_job_execution(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        JobExecution $jobExecution,
        StepInterface $step1,
        StepInterface $step2,
        BatchStatus $batchStatusStarting,
        BatchStatus $batchStatusCompleted,
        StepExecution $stepExecution1,
        StepExecution $stepExecution2,
        ExitStatus $exitStatus,
    ) {
        $steps = [
            $step1,
            $step2,
        ];

        $this->initializeNonExecutedStep('step_1', $step1, $stepExecution1, $batchStatusCompleted, $exitStatus, $jobExecution);
        $this->initializeNonExecutedStep('step_2', $step2, $stepExecution2, $batchStatusCompleted, $exitStatus, $jobExecution);

        $jobExecution->getStatus()->willReturn($batchStatusStarting);

        $calls = 0;
        $jobExecution->getStepExecutions()->will(function () use (&$calls, $stepExecution1, $stepExecution2) {
            if ($calls < 2) {
                $calls++;
                if ($calls === 2) {
                    return [$stepExecution1];
                }
                return [];
            }
            return [$stepExecution1, $stepExecution2];
        });

        $jobExecution->upgradeStatus(BatchStatus::COMPLETED)->shouldBeCalled();
        $jobExecution->setEndTime(Argument::any())->shouldBeCalled();

        $this->beConstructedWith('a_starting_job', $eventDispatcher, $jobRepository, $steps);

        $this->execute($jobExecution);
    }
    function it_executes_a_paused_job_execution(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        JobExecution $jobExecution,
        StepInterface $step1,
        StepInterface $step2,
        BatchStatus $batchStatusPaused,
        BatchStatus $batchStatusCompleted,
        StepExecution $stepExecution1,
        StepExecution $stepExecution2,
        ExitStatus $exitStatus,
    ) {
        $steps = [
            $step1,
            $step2,
        ];

        $jobExecution->getStatus()->willReturn($batchStatusPaused);

        $this->initializePausedStep('step_1', $step1, $stepExecution1, $batchStatusCompleted, $exitStatus, $jobExecution);
        $this->initializeNonExecutedStep('step_2', $step2, $stepExecution2, $batchStatusCompleted, $exitStatus, $jobExecution);

        $calls = 0;
        $jobExecution->getStepExecutions()->will(function () use (&$calls, $stepExecution1, $stepExecution2) {
            if ($calls < 2) {
                $calls++;
                return [$stepExecution1];
            }
            return [$stepExecution1, $stepExecution2];
        });

        $jobExecution->upgradeStatus(BatchStatus::COMPLETED)->shouldBeCalled();
        $jobExecution->setEndTime(Argument::any())->shouldBeCalled();

        $this->beConstructedWith('a_paused_job', $eventDispatcher, $jobRepository, $steps);

        $this->execute($jobExecution);
    }

    function it_fails_when_it_executes_a_paused_job_and_a_new_step_has_been_added_to_the_job(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        JobExecution $jobExecution,
        StepInterface $step1,
        StepInterface $step2,
        StepInterface $newStep,
        BatchStatus $batchStatusPaused,
        BatchStatus $batchStatusCompleted,
        StepExecution $stepExecution1,
        StepExecution $stepExecution2,
        ExitStatus $exitStatus,
    ) {
        $steps = [
            $newStep,
            $step1,
            $step2,
        ];

        $jobExecution->getStatus()->willReturn($batchStatusPaused);

        $newStep->getName()->willReturn('new_step');
        $this->initializePausedStep('step_1', $step1, $stepExecution1, $batchStatusCompleted, $exitStatus, $jobExecution);

        $jobExecution->getStepExecutions()->willReturn([$stepExecution1, $stepExecution2]);

        $this->assertStatusShouldBeSet($jobExecution, BatchStatus::FAILED);
        $jobExecution->addFailureException(Argument::that(static fn (\RuntimeException $e) => 'The job is corrupted' === $e->getMessage()))->shouldBeCalled();

        $this->beConstructedWith('a_paused_job_with_new_step', $eventDispatcher, $jobRepository, $steps);

        $this->execute($jobExecution);
    }

    function it_fails_when_it_executes_a_paused_job_and_a_step_has_been_removed_from_the_job(
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        JobExecution $jobExecution,
        StepInterface $step2,
        BatchStatus $batchStatusPaused,
        StepExecution $stepExecution1,
        StepExecution $stepExecution2,
    ) {
        $steps = [
            $step2,
        ];

        $jobExecution->getStatus()->willReturn($batchStatusPaused);

        $stepExecution1->getStepName()->willReturn('step_1');
        $step2->getName()->willReturn('step_2');

        $jobExecution->getStepExecutions()->willReturn([$stepExecution1, $stepExecution2]);

        $this->assertStatusShouldBeSet($jobExecution, BatchStatus::FAILED);
        $jobExecution->addFailureException(Argument::that(static fn (\RuntimeException $e) => 'The job is corrupted' === $e->getMessage()))->shouldBeCalled();

        $this->beConstructedWith('a_paused_job_with_removed_step', $eventDispatcher, $jobRepository, $steps);

        $this->execute($jobExecution);
    }

    private function assertStatusShouldBeSet(JobExecution $jobExecution, int $expectedStatus): void
    {
        $jobExecution->setStatus(Argument::that(static fn (BatchStatus $status) => $status->getValue() === $expectedStatus))->shouldBeCalled();
    }

    private function initializeNonExecutedStep(
        string $stepName,
        StepInterface $step,
        StepExecution $stepExecution,
        BatchStatus $batchStatus,
        ExitStatus $exitStatus,
        JobExecution $jobExecution,
    ): void {
        $step->getName()->willReturn($stepName);
        $stepExecution->getStepName()->willReturn($stepName);
        $stepExecution->getStatus()->willReturn($batchStatus);
        $stepExecution->getExitStatus()->willReturn($exitStatus);

        $jobExecution->createStepExecution($stepName)->shouldBeCalled()->willReturn($stepExecution);
        $step->execute($stepExecution)->shouldBeCalled();
    }

    private function initializePausedStep(
        string $stepName,
        StepInterface $step,
        StepExecution $stepExecution,
        BatchStatus $batchStatus,
        ExitStatus $exitStatus,
        JobExecution $jobExecution,
    ): void {
        $step->getName()->willReturn($stepName);
        $stepExecution->getStepName()->willReturn($stepName);
        $stepExecution->getStatus()->willReturn($batchStatus);
        $stepExecution->getExitStatus()->willReturn($exitStatus);

        $jobExecution->createStepExecution($stepName)->shouldNotBeCalled();
        $step->execute($stepExecution)->shouldNotBeCalled();
    }
}
