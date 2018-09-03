<?php

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Implementation of the {@link Job} interface.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.AbstractJob;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Job implements JobInterface
{
    /** @var string */
    protected $name;

    /* @var EventDispatcherInterface */
    protected $eventDispatcher;

    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /** @var array */
    protected $steps;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param string                   $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface   $jobRepository
     * @param StepInterface[]          $steps
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        array $steps = []
    ) {
        $this->name = $name;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository = $jobRepository;
        $this->steps = $steps;
        $this->filesystem = new Filesystem();
    }

    /**
     * Get the job's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Retrieve the step with the given name. If there is no Step with the given
     * name, then return null.
     *
     * @param string $stepName
     *
     * @return StepInterface the Step
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
        $names = [];
        foreach ($this->steps as $step) {
            $names[] = $step->getName();
        }

        return $names;
    }

    /**
     * Public getter for the {@link JobRepositoryInterface} that is needed to manage the
     * state of the batch meta domain (jobs, steps, executions) during the life
     * of a job.
     *
     * @return JobRepositoryInterface
     */
    public function getJobRepository()
    {
        return $this->jobRepository;
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
     * Run the specified job, handling all listener and repository calls, and
     * delegating the actual processing to {@link #doExecute(JobExecution)}.
     * @param JobExecution $jobExecution
     *
     * @see Job#execute(JobExecution)
     *
     * A unique working directory is created before the execution of the job. It is deleted when the job is terminated.
     * The working directory is created in the temporary filesystem. Its pathname is placed in the JobExecutionContext
     * via the key {@link \Akeneo\Tool\Component\Batch\Job\JobInterface::WORKING_DIRECTORY_PARAMETER}
     */
    final public function execute(JobExecution $jobExecution)
    {
        try {
            $workingDirectory = $this->createWorkingDirectory();
            $jobExecution->getExecutionContext()->put(JobInterface::WORKING_DIRECTORY_PARAMETER, $workingDirectory);

            $this->dispatchJobExecutionEvent(EventInterface::BEFORE_JOB_EXECUTION, $jobExecution);

            if ($jobExecution->getStatus()->getValue() !== BatchStatus::STOPPING) {
                $jobExecution->setStartTime(new \DateTime());
                $this->updateStatus($jobExecution, BatchStatus::STARTED);
                $this->jobRepository->updateJobExecution($jobExecution);

                $this->doExecute($jobExecution);
            } else {
                // The job was already stopped before we even got this far. Deal
                // with it in the same way as any other interruption.
                $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
                $jobExecution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED));
                $this->jobRepository->updateJobExecution($jobExecution);

                $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_STOPPED, $jobExecution);
            }

            if (($jobExecution->getStatus()->getValue() <= BatchStatus::STOPPED)
                && (count($jobExecution->getStepExecutions()) === 0)
            ) {
                $exitStatus = $jobExecution->getExitStatus();
                $noopExitStatus = new ExitStatus(ExitStatus::NOOP);
                $noopExitStatus->addExitDescription("All steps already completed or no steps configured for this job.");
                $jobExecution->setExitStatus($exitStatus->logicalAnd($noopExitStatus));
                $this->jobRepository->updateJobExecution($jobExecution);
            }

            $this->dispatchJobExecutionEvent(EventInterface::AFTER_JOB_EXECUTION, $jobExecution);

            $jobExecution->setEndTime(new \DateTime());
            $this->jobRepository->updateJobExecution($jobExecution);
        } catch (JobInterruptedException $e) {
            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(
                new BatchStatus(
                    BatchStatus::max(BatchStatus::STOPPED, $e->getStatus()->getValue())
                )
            );
            $jobExecution->addFailureException($e);
            $this->jobRepository->updateJobExecution($jobExecution);

            $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_INTERRUPTED, $jobExecution);
        } catch (\Exception $e) {
            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
            $jobExecution->addFailureException($e);
            $this->jobRepository->updateJobExecution($jobExecution);

            $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_FATAL_ERROR, $jobExecution);
        } finally {
            $workingDirectory = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);
            if (null !== $workingDirectory) {
                $this->deleteWorkingDirectory($workingDirectory);
            }
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
     */
    protected function doExecute(JobExecution $jobExecution)
    {
        /* @var StepExecution $stepExecution */
        $stepExecution = null;

        foreach ($this->steps as $step) {
            $stepExecution = $this->handleStep($step, $jobExecution);
            $this->jobRepository->updateStepExecution($stepExecution);

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
            $this->jobRepository->updateJobExecution($jobExecution);
        }
    }

    /**
     * Handle a step and return the execution for it.
     * @param StepInterface $step         Step
     * @param JobExecution  $jobExecution Job execution
     *
     * @throws JobInterruptedException
     *
     * @return StepExecution
     */
    protected function handleStep(StepInterface $step, JobExecution $jobExecution)
    {
        if ($jobExecution->isStopping()) {
            throw new JobInterruptedException("JobExecution interrupted.");
        }

        $stepExecution = $jobExecution->createStepExecution($step->getName());

        try {
            $step->execute($stepExecution);
        } catch (JobInterruptedException $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            $this->jobRepository->updateStepExecution($stepExecution);
            throw $e;
        }

        if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPING
            || $stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
            $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            $this->jobRepository->updateJobExecution($jobExecution);
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

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param \Exception $e the cause of the failure
     *
     * @return ExitStatus an {@link ExitStatus}
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
     * Create a unique working directory
     *
     * @return string the working directory path
     */
    private function createWorkingDirectory()
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('akeneo_batch_') . DIRECTORY_SEPARATOR;
        try {
            $this->filesystem->mkdir($path);
        } catch (IOException $e) {
            // this exception will be catched by {Job->execute()} and will set the batch as failed
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $path]);
        }

        return $path;
    }

    /**
     * Delete the working directory
     *
     * @param string $directory
     */
    private function deleteWorkingDirectory($directory)
    {
        if ($this->filesystem->exists($directory)) {
            $this->filesystem->remove($directory);
        }
    }
}
