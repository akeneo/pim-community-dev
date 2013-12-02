<?php

namespace Oro\Bundle\BatchBundle\Job;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Oro\Bundle\BatchBundle\Step\StepInterface;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;

/**
 * Implementation of the {@link Job} interface.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.AbstractJob;
 *
 */
class Job implements JobInterface
{
    protected $name;

    /* @var EventDispatcherInterface */
    protected $eventDispatcher;

    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @var array
     *
     * @Assert\Valid
     */
    protected $steps;

    /**
     * Convenience constructor to immediately add name (which is mandatory)
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name   = $name;
        $this->steps  = array();
    }

    /**
     * Get the job's name
     *
     * @return name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name property
     *
     * @param string $name
     *
     * @return Job
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return Job
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Return all the steps
     *
     * @return array steps
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Public setter for the steps in this job. Overrides any calls to
     * addStep(Step).
     *
     * @param array $steps the steps to execute
     *
     * @return Job
     */
    public function setSteps(array $steps)
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * Retrieve the step with the given name. If there is no Step with the given
     * name, then return null.
     *
     * @param string $stepName
     *
     * @return Step the Step
     */
    public function getStep($stepName)
    {
        foreach ($this->steps as $step) {
            if ($step->getName() == $stepName) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Retrieve the step names.
     *
     * @return array the step names
     */
    public function getStepNames()
    {
        $names = array();
        foreach ($this->steps as $step) {
            $names[] = $step->getName();
        }

        return $names;
    }

    /**
     * Convenience method for adding a single step to the job.
     *
     * @param StepInterface $step a {@link Step} to add
     */
    public function addStep(StepInterface $step)
    {
        $this->steps[] = $step;
    }

    /**
     * Public setter for the {@link JobRepositoryInterface} that is needed to manage the
     * state of the batch meta domain (jobs, steps, executions) during the life
     * of a job.
     *
     * @param JobRepositoryInterface $jobRepository
     */
    public function setJobRepository(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * Run the specified job, handling all listener and repository calls, and
     * delegating the actual processing to {@link #doExecute(JobExecution)}.
     * @param JobExecution $jobExecution
     *
     * @see Job#execute(JobExecution)
     * @throws StartLimitExceededException
     *             if start limit of one of the steps was exceeded
     */
    final public function execute(JobExecution $jobExecution)
    {
        $this->dispatchJobExecutionEvent(EventInterface::BEFORE_JOB_EXECUTION, $jobExecution);

        try {
            if ($jobExecution->getStatus()->getValue() !== BatchStatus::STOPPING) {
                $jobExecution->setStartTime(new \DateTime());
                $this->updateStatus($jobExecution, BatchStatus::STARTED);

                // Todo Listener beforeJob
                $this->doExecute($jobExecution);
            } else {
                // The job was already stopped before we even got this far. Deal
                // with it in the same way as any other interruption.
                $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
                $jobExecution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED));

                $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_STOPPED, $jobExecution);
            }

        } catch (JobInterruptedException $e) {
            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(
                new BatchStatus(
                    BatchStatus::max(BatchStatus::STOPPED, $e->getStatus()->getValue())
                )
            );
            $jobExecution->addFailureException($e);
            $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_INTERRUPTED, $jobExecution);
        } catch (\Exception $e) {
            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
            $jobExecution->addFailureException($e);
            $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_FATAL_ERROR, $jobExecution);
        }

        if (($jobExecution->getStatus()->getValue() <= BatchStatus::STOPPED)
            && (count($jobExecution->getStepExecutions()) === 0)
        ) {
            /* @var ExitStatus */
            $exitStatus = $jobExecution->getExitStatus();
            $noopExitStatus = new ExitStatus(ExitStatus::NOOP);
            $noopExitStatus->addExitDescription("All steps already completed or no steps configured for this job.");
            $jobExecution->setExitStatus($exitStatus->logicalAnd($noopExitStatus));
        }

        $jobExecution->setEndTime(new \DateTime());

        $this->dispatchJobExecutionEvent(EventInterface::AFTER_JOB_EXECUTION, $jobExecution);
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param Exception $e the cause of the failure
     *
     * @return an {@link ExitStatus}
     */
    private function getDefaultExitStatusForFailure(\Exception $e)
    {
        $exitStatus = new ExitStatus();

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
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param JobExecution $jobExecution Execution of the job
     * @param string       $status       Status of the execution
     *
     * @return an {@link ExitStatus}
     */
    private function updateStatus(JobExecution $jobExecution, $status)
    {
        $jobExecution->setStatus(new BatchStatus($status));
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . ': [name=' . $this->name . ']';
    }

    /**
     * Get the steps configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        $result = array();
        foreach ($this->steps as $step) {
            $result[$step->getName()] = $step->getConfiguration();
        }

        return $result;
    }

    /**
     * Set the steps configuration
     *
     * @param array $steps
     */
    public function setConfiguration(array $steps)
    {
        foreach ($steps as $title => $config) {
            $step = $this->getStep($title);
            if (!$step) {
                throw new \InvalidArgumentException(sprintf('Unknown step "%s"', $title));
            }

            $step->setConfiguration($config);
        }
    }

    /**
     * Handler of steps sequentially as provided, checking each one for success
     * before moving to the next. Returns the last {@link StepExecution}
     * successfully processed if it exists, and null if none were processed.
     *
     * @param JobExecution $jobExecution the current {@link JobExecution}
     *
     * @throws JobInterruptedException
     * @throws JobRestartException
     * @throws StartLimitExceededException
     */
    protected function doExecute(JobExecution $jobExecution)
    {
        /* @var StepExecution $stepExecution */
        $stepExecution = null;

        foreach ($this->steps as $step) {
            $stepExecution = $this->handleStep($step, $jobExecution);

            if ($stepExecution->getStatus()->getValue() !== BatchStatus::COMPLETED) {
                // Terminate the job if a step fails
                break;
            }
        }

        // Update the job status to be the same as the last step
        if ($stepExecution !== null) {
            $this->dispatchJobExecutionEvent(EventInterface::BEFORE_JOB_STATUS_UPGRADE, $jobExecution);
            $jobExecution->upgradeStatus($stepExecution->getStatus()->getValue());
            $jobExecution->setExitStatus($stepExecution->getExitStatus());
        }
    }

    /**
     * Handle a step and return the execution for it.
     * @param StepInterface $step         Step
     * @param JobExecution  $jobExecution Job execution
     *
     * @throws JobInterruptedException
     * @throws JobRestartException
     * @throws StartLimitExceededException
     *
     * @return StepExecution
     */
    public function handleStep(StepInterface $step, JobExecution $jobExecution)
    {
        if ($jobExecution->isStopping()) {
            throw new JobInterruptedException("JobExecution interrupted.");
        }

        $stepExecution = $jobExecution->createStepExecution($step->getName());

        try {
            $step->execute($stepExecution);
        } catch (JobInterruptedException $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw $e;
        }

        if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPING
            || $stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
            $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw new JobInterruptedException("Job interrupted by step execution");
        }

        return $stepExecution;
    }

    /**
     * Trigger event linked to JobExecution
     *
     * @param string       $eventName    Name of the event
     * @param JobExecution $jobExecution Object to store job execution
     */
    private function dispatchJobExecutionEvent($eventName, JobExecution $jobExecution)
    {
        $event = new JobExecutionEvent($jobExecution);
        $this->dispatch($eventName, $event);
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
