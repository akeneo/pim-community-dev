<?php

namespace Akeneo\Tool\Component\Batch\Step;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobInterruptedException;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Job\JobStopperInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * A Step implementation that provides common behavior to subclasses, including registering and calling
 * listeners.
 *
 * Inspired by Spring Batch org.springframework.batch.core.step.AbstractStep;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractStep implements StepInterface
{
    public function __construct(
        protected string $name,
        protected EventDispatcherInterface $eventDispatcher,
        protected JobRepositoryInterface $jobRepository,
        private ?JobStopperInterface $jobStopper = null,
    ) {
    }

    /**
     * @return JobRepositoryInterface
     */
    public function getJobRepository()
    {
        return $this->jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning.
     *
     * Do not catch exception here. It will be correctly handled by the execute() method.
     *
     * @throws \Exception
     */
    abstract protected function doExecute(StepExecution $stepExecution);

    /**
     * Template method for step execution logic
     *
     * @throws JobInterruptedException
     */
    final public function execute(StepExecution $stepExecution)
    {
        $this->dispatchStepExecutionEvent(EventInterface::BEFORE_STEP_EXECUTION, $stepExecution);

        $stepExecution->setStartTime(new \DateTime());
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STARTED));
        $this->jobRepository->updateStepExecution($stepExecution);

        // Start with a default value that will be trumped by anything
        $exitStatus = new ExitStatus(ExitStatus::EXECUTING);

        try {
            $this->doExecute($stepExecution);

            $exitStatus = new ExitStatus(ExitStatus::COMPLETED);
            $exitStatus->logicalAnd($stepExecution->getExitStatus());

            $this->jobRepository->updateStepExecution($stepExecution);

            // Check if someone is trying to stop us
            if ($stepExecution->isTerminateOnly()) {
                throw new JobInterruptedException("JobExecution interrupted.");
            }

            // Need to upgrade here not set, in case the execution was stopped
            $stepExecution->upgradeStatus(BatchStatus::COMPLETED);
            $this->dispatchStepExecutionEvent(EventInterface::STEP_EXECUTION_SUCCEEDED, $stepExecution);
        } catch (\Exception $e) {
            $stepExecution->upgradeStatus($this->determineBatchStatus($e));

            $exitStatus = $exitStatus->logicalAnd($this->getDefaultExitStatusForFailure($e));
            $stepExecution->addFailureException($e);
            $this->jobRepository->updateStepExecution($stepExecution);

            if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
                $this->dispatchStepExecutionEvent(EventInterface::STEP_EXECUTION_INTERRUPTED, $stepExecution, $e);
            } else {
                $this->dispatchStepExecutionEvent(EventInterface::STEP_EXECUTION_ERRORED, $stepExecution, $e);
            }
        }

        $this->dispatchStepExecutionEvent(EventInterface::STEP_EXECUTION_COMPLETED, $stepExecution);

        $stepExecution->setEndTime(new \DateTime());
        $stepExecution->setExitStatus($exitStatus);
        $this->jobRepository->updateStepExecution($stepExecution);
    }

    private static function determineBatchStatus(\Exception $e): int
    {
        if ($e instanceof JobInterruptedException || $e->getPrevious() instanceof JobInterruptedException) {
            return BatchStatus::STOPPED;
        } else {
            return BatchStatus::FAILED;
        }
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     */
    private function getDefaultExitStatusForFailure(\Exception $e): ExitStatus
    {
        if ($e instanceof JobInterruptedException || $e->getPrevious() instanceof JobInterruptedException) {
            $exitStatus = new ExitStatus(ExitStatus::STOPPED);
            $exitStatus->addExitDescription(get_class(new JobInterruptedException()));
        } else {
            $exitStatus = new ExitStatus(ExitStatus::FAILED);
            $exitStatus->addExitDescription($e);
        }

        return $exitStatus;
    }

    /**
     * Trigger event linked to Step
     *
     * @param string        $eventName     Name of the event
     * @param StepExecution $stepExecution Step object
     */
    protected function dispatchStepExecutionEvent($eventName, StepExecution $stepExecution, \Exception $exception = null)
    {
        $event = new StepExecutionEvent($stepExecution, $exception);
        $this->dispatch($event, $eventName);
    }

    /**
     * Trigger an invalid item event
     *
     * @param string $class
     * @param string $reason
     * @param array  $reasonParameters
     * @param InvalidItemInterface  $item
     */
    protected function dispatchInvalidItemEvent($class, $reason, array $reasonParameters, InvalidItemInterface $item)
    {
        $event = new InvalidItemEvent($item, $class, $reason, $reasonParameters);
        $this->dispatch($event, EventInterface::INVALID_ITEM);
    }

    private function dispatch(Event $event, $eventName): void
    {
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}
