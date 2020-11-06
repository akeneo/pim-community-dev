<?php

namespace Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\RuntimeErrorException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;

/**
 * Batch domain object representation the execution of a step. Unlike JobExecution, there are additional properties
 * related the processing of items such as commit count, etc.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.StepExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class StepExecution
{
    /** @var integer */
    private $id;

    /** @var JobExecution */
    private $jobExecution = null;

    /** @var string */
    private $stepName;

    /** @var integer */
    private $status = null;

    /** @var integer */
    private $readCount = 0;

    /** @var integer */
    private $writeCount = 0;

    /** @var integer */
    private $filterCount = 0;

    /** @var \DateTime */
    private $startTime;

    /** @var \DateTime */
    private $endTime;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus */
    private $exitStatus = null;

    /** @var string */
    private $exitCode = null;

    /** @var string */
    private $exitDescription = null;

    /** @var boolean */
    private $terminateOnly = false;

    /** @var array */
    private $failureExceptions = null;

    /** @var array */
    private $errors = [];

    /** @var ArrayCollection */
    private $warnings;

    /** @var array */
    private $summary = [];

    /**
     * Constructor with mandatory properties.
     *
     * @param string       $stepName     the step to which this execution belongs
     * @param JobExecution $jobExecution the current job execution
     */
    public function __construct(string $stepName, JobExecution $jobExecution)
    {
        $this->stepName = $stepName;
        $this->jobExecution = $jobExecution;
        $jobExecution->addStepExecution($this);
        $this->warnings = new ArrayCollection();
        $this->executionContext = new ExecutionContext();
        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::EXECUTING));

        $this->failureExceptions = [];
        $this->errors = [];

        $this->startTime = new \DateTime();
    }

    /**
     * Reset id on clone
     */
    public function __clone()
    {
        $this->id = null;
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
     * @return \DateTime time that this execution ended
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
     * Returns the current number of items read for this execution
     *
     * @return integer the current number of items read for this execution
     */
    public function getReadCount(): int
    {
        return $this->readCount;
    }

    /**
     * Sets the current number of read items for this execution
     *
     * @param integer $readCount the current number of read items for this execution
     */
    public function setReadCount(int $readCount): self
    {
        $this->readCount = $readCount;

        return $this;
    }

    /**
     * Increment the read count by 1
     */
    public function incrementReadCount(): void
    {
        $this->readCount++;
    }

    /**
     * Returns the current number of items written for this execution
     *
     * @return integer the current number of items written for this execution
     */
    public function getWriteCount(): int
    {
        return $this->writeCount;
    }

    /**
     * Sets the current number of written items for this execution
     *
     * @param integer $writeCount the current number of written items for this execution
     */
    public function setWriteCount(int $writeCount): self
    {
        $this->writeCount = $writeCount;

        return $this;
    }

    /**
     * Increment the write count by 1
     */
    public function incrementWriteCount(): void
    {
        $this->writeCount++;
    }

    /**
     * Returns the current number of items filtered out of this execution
     *
     * @return integer the current number of items filtered out of this execution
     */
    public function getFilterCount(): int
    {
        return $this->readCount - $this->writeCount;
    }

    /**
     * @return boolean flag to indicate that an execution should halt
     */
    public function isTerminateOnly(): bool
    {
        return $this->terminateOnly;
    }

    /**
     * Set a flag that will signal to an execution environment that this
     * execution (and its surrounding job) wishes to exit.
     */
    public function setTerminateOnly(): self
    {
        $this->terminateOnly = true;

        return $this;
    }

    /**
     * Gets the time this execution started
     *
     * @return \DateTime The time this execution started
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
     * @return string the name of the step
     */
    public function getStepName(): string
    {
        return $this->stepName;
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
     * @return ExitStatus the exit status
     */
    public function getExitStatus(): \Akeneo\Tool\Component\Batch\Job\ExitStatus
    {
        return $this->exitStatus;
    }

    /**
     * Accessor for the execution context information of the enclosing job.
     *
     * @return JobExecution the job execution that was used to start this step execution.
     *
     */
    public function getJobExecution(): \Akeneo\Tool\Component\Batch\Model\JobExecution
    {
        return $this->jobExecution;
    }

    /**
     * Accessor for the job parameters
     *
     *
     */
    public function getJobParameters(): ?JobParameters
    {
        return $this->jobExecution->getJobParameters();
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

    public function getFailureExceptionMessages(): string
    {
        return implode(
            ' ',
            array_map(
                fn($e) => $e['message'],
                $this->failureExceptions
            )
        );
    }

    /**
     * @param string $message
     */
    public function addError(string $message): self
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Add a warning
     *
     * @param string               $reason
     * @param array                $reasonParameters
     * @param InvalidItemInterface $item
     */
    public function addWarning(string $reason, array $reasonParameters, InvalidItemInterface $item): void
    {
        $data = $item->getInvalidData();

        if (null === $data) {
            $data = [];
        }

        if (is_object($data)) {
            $data = [
                'class'  => ClassUtils::getClass($data),
                'id'     => method_exists($data, 'getId') ? $data->getId() : '[unknown]',
                'string' => method_exists($data, '__toString') ? (string) $data : '[unknown]',
            ];
        }

        $this->warnings->add(
            new Warning(
                $this,
                $reason,
                $reasonParameters,
                $data
            )
        );
    }

    /**
     * Get the warnings
     */
    public function getWarnings(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->warnings;
    }

    /**
     * Add row in summary
     *
     * @param string $key
     * @param mixed  $info
     */
    public function addSummaryInfo(string $key, $info): void
    {
        $this->summary[$key] = $info;
    }

    /**
     * Increment counter in summary
     *
     * @param string  $key
     * @param integer $increment
     */
    public function incrementSummaryInfo(string $key, int $increment = 1): void
    {
        $this->summary[$key] = !isset($this->summary[$key]) ? $increment : $this->summary[$key] + $increment;
    }

    /**
     * Get a summary row
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSummaryInfo(string $key)
    {
        return isset($this->summary[$key]) ? $this->summary[$key] : '';
    }

    /**
     * Set summary
     *
     * @param array $summary
     */
    public function setSummary(array $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     */
    public function getSummary(): array
    {
        return $this->summary;
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $summary = 'id=%d, name=[%s], status=[%s], exitCode=[%s], exitDescription=[%s]';

        return sprintf(
            $summary,
            $this->id,
            $this->stepName,
            $this->status,
            $this->exitCode,
            $this->exitDescription
        );
    }
}
