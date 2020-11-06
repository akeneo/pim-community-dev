<?php

namespace Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\RuntimeErrorException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Batch domain object representing the execution of a job
 *
 * Inspired by Spring Batch  org.springframework.batch.job.JobExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobExecution
{
    /** @var integer */
    private $id;

    /** @var ArrayCollection */
    private $stepExecutions;

    /** @var JobInstance */
    private $jobInstance;

    /** @var integer Process Identifier */
    private $pid;

    /** @var string|null The user who launched the job */
    private $user;

    /** @var integer */
    private $status;

    /** @var \DateTime */
    private $startTime;

    /** @var \DateTime */
    private $endTime;

    /** @var \DateTime */
    private $createTime;

    /** @var \DateTime */
    private $updatedTime;

    /** @var \DateTime */
    private $healthCheckTime;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus $existStatus */
    private $exitStatus;

    /** @var string */
    private $exitCode;

    /** @var string */
    private $exitDescription;

    /** @var array */
    private $failureExceptions;

    /** @var string */
    private $logFile;

    /** @var JobParameters */
    private $jobParameters;

    /** @var array */
    private $rawParameters;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::UNKNOWN));
        $this->executionContext = new ExecutionContext();
        $this->stepExecutions = new ArrayCollection();
        $this->createTime = new \DateTime();
        $this->failureExceptions = [];
        $this->rawParameters = [];
    }

    /**
     * Clones the step executions and execution context along with the JobExecution
     */
    public function __clone()
    {
        $this->id = null;

        $this->stepExecutions = clone $this->stepExecutions;

        $this->executionContext = clone $this->executionContext;
    }

    /**
     * Get Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the {@link ExecutionContext} for this execution
     *
     * @return ExecutionContext with its attributes
     */
    public function getExecutionContext(): \Akeneo\Tool\Component\Batch\Item\ExecutionContext
    {
        return $this->executionContext;
    }

    /**
     * Sets the {@link ExecutionContext} for this execution
     *
     * @param ExecutionContext $executionContext the attributes
     */
    public function setExecutionContext(ExecutionContext $executionContext): self
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * Returns the time that this execution ended
     *
     * @return \DateTime the time that this execution ended
     */
    public function getEndTime(): \DateTime
    {
        return $this->endTime;
    }

    /**
     * Sets the time that this execution ended
     *
     * @param \DateTime $endTime the time that this execution ended
     */
    public function setEndTime(\DateTime $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Gets the time this execution started
     *
     * @return \DateTime the time this execution started
     */
    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    /**
     * Sets the time this execution started
     *
     * @param \DateTime $startTime the time this execution started
     */
    public function setStartTime(\DateTime $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }
    /**
     * Gets the time this execution has been created
     *
     * @return \DateTime the time this execution has been created
     */
    public function getCreateTime(): \DateTime
    {
        return $this->createTime;
    }

    /**
     * Sets the time this execution has been created
     *
     * @param \DateTime $createTime the time this execution has been created
     */
    public function setCreateTime(\DateTime $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Gets the time this execution has been updated
     *
     * @return \DateTime time this execution has been updated
     */
    public function getUpdatedTime(): \DateTime
    {
        return $this->updatedTime;
    }

    /**
     * Sets the time this execution has been updated
     *
     * @param \DateTime $updatedTime the time this execution has been updated
     */
    public function setUpdatedTime(\DateTime $updatedTime): self
    {
        $this->updatedTime = $updatedTime;

        return $this;
    }

    /**
      * Gets the time this execution has been health checked
      *
      * @return \DateTime time this execution has been health checked
      */
    public function getHealthCheckTime(): \DateTime
    {
        return $this->healthCheckTime;
    }

    /**
     * Sets the time this execution has been health checked
     *
     * @param \DateTime $healthCheckTime the time this execution has been health checked
     */
    public function setHealthcheckTime(\DateTime $healthCheckTime): self
    {
        $this->healthCheckTime= $healthCheckTime;

        return $this;
    }

    /**
     * Returns the process identifier of the batch job
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * Sets the process identifier of the batch job
     *
     * @param integer $pid
     */
    public function setPid(int $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Returns the user who launched the job
     *
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * Sets the user who launched the job
     *
     * @param string $user
     */
    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns the current status of this step
     *
     * @return BatchStatus the current status of this step
     */
    public function getStatus(): \Akeneo\Tool\Component\Batch\Job\BatchStatus
    {
        return new BatchStatus($this->status);
    }

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     */
    public function setStatus(BatchStatus $status): self
    {
        $this->status = $status->getValue();

        return $this;
    }

    /**
     * Upgrade the status field if the provided value is greater than the
     * existing one. Clients using this method to set the status can be sure
     * that they don't overwrite a failed status with an successful one.
     *
     * @param mixed $status the new status value
     */
    public function upgradeStatus($status): self
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @param ExitStatus $exitStatus
     */
    public function setExitStatus(ExitStatus $exitStatus): self
    {
        $this->exitStatus = $exitStatus;
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();

        return $this;
    }

    /**
     * @return ExitStatus exitCode
     */
    public function getExitStatus(): ?\Akeneo\Tool\Component\Batch\Job\ExitStatus
    {
        if ($this->exitStatus === null && $this->exitCode !== null) {
            $this->exitStatus = new ExitStatus($this->exitCode);
        }

        return $this->exitStatus;
    }

    /**
     * Accessor for the step executions.
     *
     * @return ArrayCollection|StepExecution[] the step executions that were registered
     */
    public function getStepExecutions(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->stepExecutions;
    }

    /**
     * Register a step execution with the current job execution.
     *
     * @param mixed $stepName the name of the step the new execution is associated with
     *
     * @return StepExecution the created stepExecution
     */
    public function createStepExecution($stepName): \Akeneo\Tool\Component\Batch\Model\StepExecution
    {
        return new StepExecution($stepName, $this);
    }

    /**
     * Add a step executions to job's step execution
     *
     * @param StepExecution $stepExecution
     */
    public function addStepExecution(StepExecution $stepExecution): self
    {
        $this->stepExecutions[] = $stepExecution;

        return $this;
    }

    /**
     * Test if this JobExecution indicates that it is running. It should
     * be noted that this does not necessarily mean that it has been persisted
     * as such yet.
     *
     * @return bool if the end time is null
     */
    public function isRunning(): bool
    {
        return $this->getStatus()->isRunning();
    }

    /**
     * Test if this JobExecution indicates that it has been signalled to
     * stop.
     * @return bool if the status is BatchStatus::STOPPING
     */
    public function isStopping(): bool
    {
        return $this->status == BatchStatus::STOPPING;
    }

    /**
     * Signal the JobExecution to stop. Iterates through the associated
     * StepExecution, calling StepExecution::setTerminateOnly().
     */
    public function stop(): self
    {
        /** @var StepExecution $stepExecution */
        foreach ($this->stepExecutions as $stepExecution) {
            $stepExecution->setTerminateOnly();
        }
        $this->status = BatchStatus::STOPPING;

        return $this;
    }

    /**
     * Get failure exceptions
     * @return mixed
     */
    public function getFailureExceptions(): array
    {
        return $this->failureExceptions;
    }

    /**
     * Add a failure exception
     * @param \Exception $e
     */
    public function addFailureException(\Exception $e): self
    {
        $this->failureExceptions[] = [
            'class'             => get_class($e),
            'message'           => $e->getMessage(),
            'messageParameters' => $e instanceof RuntimeErrorException ? $e->getMessageParameters() : [],
            'code'              => $e->getCode(),
            'trace'             => $e->getTraceAsString()
        ];

        return $this;
    }

    /**
     * Return all failure causing exceptions for this JobExecution, including
     * step executions.
     *
     * @return array containing all exceptions causing failure for this JobExecution.
     */
    public function getAllFailureExceptions(): array
    {
        $allExceptions = $this->failureExceptions;

        /** @var StepExecution $stepExecution */
        foreach ($this->stepExecutions as $stepExecution) {
            $allExceptions = array_merge($allExceptions, $stepExecution->getFailureExceptions());
        }

        return $allExceptions;
    }

    /**
     * Set the associated job
     *
     * @param JobInstance $jobInstance The job instance to associate the JobExecution to
     */
    public function setJobInstance(JobInstance $jobInstance): self
    {
        $this->jobInstance = $jobInstance;
        $this->jobInstance->addJobExecution($this);

        return $this;
    }

    /**
     * Get the associated jobInstance
     *
     * @return JobInstance The job to which the JobExecution is associated
     */
    public function getJobInstance(): \Akeneo\Tool\Component\Batch\Model\JobInstance
    {
        return $this->jobInstance;
    }

    /**
     * Get the associated jobInstance label
     */
    public function getLabel(): string
    {
        return $this->jobInstance->getLabel();
    }

    /**
     * Set the log file
     *
     * @param string $logFile
     */
    public function setLogFile(string $logFile): self
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * Get the log file
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $startTime = self::formatDate($this->startTime);
        $endTime = self::formatDate($this->endTime);
        $updatedTime = self::formatDate($this->updatedTime);
        $jobInstanceCode = $this->jobInstance != null ? $this->jobInstance->getCode() : '';

        $message = "startTime=%s, endTime=%s, updatedTime=%s, status=%d, exitStatus=%s, exitDescription=[%s], job=[%s]";

        return sprintf(
            $message,
            $startTime,
            $endTime,
            $updatedTime,
            $this->status,
            $this->exitStatus,
            $this->exitDescription,
            $jobInstanceCode
        );
    }

    /**
     * Format a date or return empty string if null
     *
     * @param \DateTime $date
     * @param string    $format
     *
     * @return string Date formatted
     */
    public static function formatDate(\DateTime $date = null, string $format = \DateTime::ATOM): string
    {
        $formattedDate = '';

        if ($date != null) {
            $formattedDate = $date->format($format);
        }

        return $formattedDate;
    }

    /**
     * @param JobParameters $jobParameters
     */
    public function setJobParameters(JobParameters $jobParameters): self
    {
        $this->jobParameters = $jobParameters;
        $this->rawParameters = $jobParameters->all();

        return $this;
    }

    public function getJobParameters(): \Akeneo\Tool\Component\Batch\Job\JobParameters
    {
        return $this->jobParameters;
    }

    /**
     * @return array|null
     */
    public function getRawParameters(): array
    {
        return $this->rawParameters;
    }
}
