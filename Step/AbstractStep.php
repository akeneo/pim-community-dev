<?php

namespace Akeneo\Bundle\BatchBundle\Step;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Akeneo\Bundle\BatchBundle\Job\ExitStatus;
use Akeneo\Bundle\BatchBundle\Job\JobRepositoryInterface;
use Akeneo\Bundle\BatchBundle\Job\JobInterruptedException;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Event\StepExecutionEvent;
use Akeneo\Bundle\BatchBundle\Event\InvalidItemEvent;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;

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
    /**
     * @var string
     */
    private $name;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var JobRepositoryInterface
     */
    private $jobRepository;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Set the event dispatcher
     *
     * @param  EventDispatcherInterface $eventDispatcher
     * @return AbstractStep
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Public setter for {@link JobRepositoryInterface}.
     *
     * @param  JobRepositoryInterface $jobRepository jobRepository is a mandatory dependence (no default).
     * @return AbstractStep
     */
    public function setJobRepository(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;

        return $this;
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
     * Set the name property
     *
     * @param  string       $name
     * @return AbstractStep
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning.
     *
     * @param  StepExecution $stepExecution the current step context
     * @throws \Exception
     */
    abstract protected function doExecute(StepExecution $stepExecution);

    /**
     * Provide the configuration of the step
     *
     * @return array
     */
    abstract public function getConfiguration();

    /**
     * Set the configuration for the step
     *
     * @param array $config
     */
    abstract public function setConfiguration(array $config);

    /**
     * Get the configurable step elements
     *
     * @return array
     */
    public function getConfigurableStepElements()
    {
        return array();
    }

    /**
     * Template method for step execution logic
     *
     * @param StepExecution $stepExecution
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
                $this->dispatchStepExecutionEvent(EventInterface::STEP_EXECUTION_INTERRUPTED, $stepExecution);
            } else {
                $this->dispatchStepExecutionEvent(EventInterface::STEP_EXECUTION_ERRORED, $stepExecution);
            }
        }

        $this->dispatchStepExecutionEvent(EventInterface::STEP_EXECUTION_COMPLETED, $stepExecution);

        $stepExecution->setEndTime(new \DateTime());
        $stepExecution->setExitStatus($exitStatus);
        $this->jobRepository->updateStepExecution($stepExecution);
    }

    /**
     * Determine the step status based on the exception.
     * @param  \Exception $e
     * @return int
     */
    private static function determineBatchStatus(\Exception $e)
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
     *
     * @param  \Exception $e the cause of the failure
     * @return ExitStatus {@link ExitStatus}
     */
    private function getDefaultExitStatusForFailure(\Exception $e)
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
    protected function dispatchStepExecutionEvent($eventName, StepExecution $stepExecution)
    {
        $event = new StepExecutionEvent($stepExecution);
        $this->dispatch($eventName, $event);
    }

    protected function dispatchInvalidItemEvent($class, $reason, array $reasonParameters, array $item)
    {
        $event = new InvalidItemEvent($class, $reason, $reasonParameters, $item);
        $this->dispatch(EventInterface::INVALID_ITEM, $event);
    }

    /**
     * Generic batch event dispatcher
     *
     * @param string $eventName Name of the event
     * @param Event  $event     Event object
     */
    private function dispatch($eventName, Event $event)
    {
        $this->eventDispatcher->dispatch($eventName, $event);
    }
}
