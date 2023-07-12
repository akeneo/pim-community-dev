<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Akeneo\Tool\Component\Batch\Step\StoppableStepInterface;
use Akeneo\Tool\Component\Batch\Step\TrackableStepInterface;
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
class Job implements JobInterface, StoppableJobInterface, PausableJobInterface, JobWithStepsInterface, VisibleJobInterface
{
    protected string $name;
    protected EventDispatcherInterface $eventDispatcher;
    protected JobRepositoryInterface $jobRepository;
    protected array $steps;
    protected bool $isStoppable;
    protected bool $isVisible;
    protected bool $isPausable;
    protected Filesystem $filesystem;

    public function __construct(
        string $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        array $steps = [],
        bool $isStoppable = false,
        bool $isVisible = true,
        bool $isPausable = false
    ) {
        $this->name = $name;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository = $jobRepository;
        $this->steps = $steps;
        $this->isStoppable = $isStoppable;
        $this->isVisible = $isVisible;
        $this->isPausable = $isPausable;
        $this->filesystem = new Filesystem();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Retrieve the step with the given name. If there is no Step with the given
     * name, then return null.
     */
    public function getStep(string $stepName): ?StepInterface
    {
        foreach ($this->steps as $step) {
            if ($step->getName() == $stepName) {
                return $step;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getStepNames(): array
    {
        $names = [];
        foreach ($this->steps as $step) {
            $names[] = $step->getName();
        }

        return $names;
    }

    public function getJobRepository(): JobRepositoryInterface
    {
        return $this->jobRepository;
    }

    public function __toString(): string
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
    final public function execute(JobExecution $jobExecution): void
    {
        try {
            $workingDirectory = $this->createWorkingDirectory();
            $jobExecution->getExecutionContext()->put(JobInterface::WORKING_DIRECTORY_PARAMETER, $workingDirectory);

            $this->dispatchJobExecutionEvent(EventInterface::BEFORE_JOB_EXECUTION, $jobExecution);

            if ($jobExecution->getStatus()->isStarting()) {
                $jobExecution->setStartTime(new \DateTime());
            }

            if ($jobExecution->getStatus()->getValue() !== BatchStatus::STOPPING) {
                $this->updateStatus($jobExecution, BatchStatus::STARTED);
                $this->jobRepository->updateJobExecution($jobExecution);

                $this->doExecute($jobExecution);
            } else {
                // The job was already stopped before we even got this far. Deal
                // with it in the same way as any other interruption.
                $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
                $jobExecution->setExitStatus(new ExitStatus(ExitStatus::STOPPED));
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

            if (!$jobExecution->getStatus()->isPaused()) {
                $jobExecution->setEndTime(new \DateTime());
            }
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

    public function isStoppable(): bool
    {
        return $this->isStoppable;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function isPausable(): bool
    {
        return $this->isPausable;
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

        foreach ($this->steps as $index => $step) {
            $stepExecution = $this->getStepExecution($jobExecution, $index);

            if (!$this->isRunnable($stepExecution)) {
                continue;
            }

            $stepExecution = $this->handleStep($step, $jobExecution, $stepExecution);
            $this->jobRepository->updateStepExecution($stepExecution);

            if ($stepExecution->getStatus()->getValue() !== BatchStatus::COMPLETED) {
                // Terminate the job if a step fails
                break;
            }
        }

        if ($stepExecution !== null && BatchStatus::STOPPED === $stepExecution->getStatus()->getValue()) {
            $jobExecution->setStatus($stepExecution->getStatus());
            $jobExecution->setExitStatus($stepExecution->getExitStatus());
            $this->jobRepository->updateJobExecution($jobExecution);

            return;
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
     *
     * @throws JobInterruptedException
     */
    protected function handleStep(StepInterface $step, JobExecution $jobExecution, ?StepExecution $stepExecution): StepExecution
    {
        if ($jobExecution->isStopping()) {
            throw new JobInterruptedException("JobExecution interrupted.");
        }

        if ($stepExecution === null) {
            $stepExecution = $jobExecution->createStepExecution($step->getName());
            $stepExecution->setStartTime(new \DateTime());
        }

        try {
            if ($step instanceof StoppableStepInterface) {
                $step->setStoppable($this->isStoppable);
            }

            if ($step instanceof TrackableStepInterface) {
                $stepExecution->setIsTrackable(true);
                $this->jobRepository->updateStepExecution($stepExecution);
            }

            $step->execute($stepExecution);
        } catch (JobInterruptedException $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            $this->jobRepository->updateStepExecution($stepExecution);
            throw $e;
        }

        if (BatchStatus::STOPPED === $stepExecution->getStatus()->getValue()) {
            $this->dispatchJobExecutionEvent(EventInterface::BEFORE_JOB_STATUS_UPGRADE, $jobExecution);

            $jobExecution->setStatus($stepExecution->getStatus());
            $jobExecution->setExitStatus($stepExecution->getExitStatus());
            $this->jobRepository->updateJobExecution($jobExecution);

            return $stepExecution;
        }

        if (
            $stepExecution->getStatus()->getValue() === BatchStatus::STOPPING &&
            $stepExecution->getExitStatus()->getExitCode() !== ExitStatus::STOPPED
        ) {
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
        $this->eventDispatcher->dispatch($event, $eventName);
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
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     */
    private function updateStatus(JobExecution $jobExecution, int $status): void
    {
        $jobExecution->setStatus(new BatchStatus($status));
    }

    /**
     * Create a unique working directory
     */
    private function createWorkingDirectory(): string
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

    private function deleteWorkingDirectory(string $directory)
    {
        if ($this->filesystem->exists($directory)) {
            $this->filesystem->remove($directory);
        }
    }

    private function isRunnable(?StepExecution $stepExecution): bool
    {
        return null === $stepExecution || in_array($stepExecution->getStatus()->getValue(), [BatchStatus::STARTING, BatchStatus::PAUSED]);
    }

    private function getStepExecution(JobExecution $jobExecution, int $index): ?StepExecution
    {
        $stepExecution = $jobExecution->getStepExecutions()[$index] ?? null;

        if (null === $stepExecution) {
            return null;
        }

        if ($stepExecution->getStepName() !== $this->steps[$index]->getName()) {
            throw new \RuntimeException("Can't resume the job because steps configuration has changed during pause.");
        }

        $stepExecution->setExecutionContext(new ExecutionContext());

        return $stepExecution;
    }
}
