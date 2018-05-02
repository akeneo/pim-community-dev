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
    public function __construct($stepName, JobExecution $jobExecution)
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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the {@link ExecutionContext} for this execution
     *
     * @return ExecutionContext with its attributes
     */
    public function getExecutionContext()
    {
        return $this->executionContext;
    }

    /**
     * Sets the {@link ExecutionContext} for this execution
     *
     * @param ExecutionContext $executionContext the attributes
     *
     * @return StepExecution
     */
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * Returns the time that this execution ended
     *
     * @return \DateTime time that this execution ended
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Sets the time that this execution ended
     *
     * @param \DateTime $endTime the time that this execution ended
     *
     * @return StepExecution
     */
    public function setEndTime(\DateTime $endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Returns the current number of items read for this execution
     *
     * @return integer the current number of items read for this execution
     */
    public function getReadCount()
    {
        return $this->readCount;
    }

    /**
     * Sets the current number of read items for this execution
     *
     * @param integer $readCount the current number of read items for this execution
     *
     * @return StepExecution
     */
    public function setReadCount($readCount)
    {
        $this->readCount = $readCount;

        return $this;
    }

    /**
     * Increment the read count by 1
     */
    public function incrementReadCount()
    {
        $this->readCount++;
    }

    /**
     * Returns the current number of items written for this execution
     *
     * @return integer the current number of items written for this execution
     */
    public function getWriteCount()
    {
        return $this->writeCount;
    }

    /**
     * Sets the current number of written items for this execution
     *
     * @param integer $writeCount the current number of written items for this execution
     *
     * @return StepExecution
     */
    public function setWriteCount($writeCount)
    {
        $this->writeCount = $writeCount;

        return $this;
    }

    /**
     * Increment the write count by 1
     */
    public function incrementWriteCount()
    {
        $this->writeCount++;
    }

    /**
     * Returns the current number of items filtered out of this execution
     *
     * @return integer the current number of items filtered out of this execution
     */
    public function getFilterCount()
    {
        return $this->readCount - $this->writeCount;
    }

    /**
     * @return boolean flag to indicate that an execution should halt
     */
    public function isTerminateOnly()
    {
        return $this->terminateOnly;
    }

    /**
     * Set a flag that will signal to an execution environment that this
     * execution (and its surrounding job) wishes to exit.
     *
     * @return StepExecution
     */
    public function setTerminateOnly()
    {
        $this->terminateOnly = true;

        return $this;
    }

    /**
     * Gets the time this execution started
     *
     * @return \DateTime The time this execution started
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Sets the time this execution started
     *
     * @param \DateTime $startTime the time this execution started
     *
     * @return StepExecution
     */
    public function setStartTime(\DateTime $startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Returns the current status of this step
     *
     * @return BatchStatus the current status of this step
     */
    public function getStatus()
    {
        return new BatchStatus($this->status);
    }

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     *
     * @return StepExecution
     */
    public function setStatus(BatchStatus $status)
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
     *
     * @return StepExecution
     */
    public function upgradeStatus($status)
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @return string the name of the step
     */
    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * @param ExitStatus $exitStatus
     *
     * @return StepExecution
     */
    public function setExitStatus(ExitStatus $exitStatus)
    {
        $this->exitStatus = $exitStatus;
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();

        return $this;
    }

    /**
     * @return ExitStatus the exit status
     */
    public function getExitStatus()
    {
        return $this->exitStatus;
    }

    /**
     * Accessor for the execution context information of the enclosing job.
     *
     * @return JobExecution the job execution that was used to start this step execution.
     *
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
    }

    /**
     * Accessor for the job parameters
     *
     * @return JobParameters
     *
     */
    public function getJobParameters()
    {
        return $this->jobExecution->getJobParameters();
    }

    /**
     * Get failure exceptions
     * @return mixed
     */
    public function getFailureExceptions()
    {
        return $this->failureExceptions;
    }

    /**
     * Add a failure exception
     * @param \Exception $e
     *
     * @return StepExecution
     */
    public function addFailureException(\Exception $e)
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
     * @return string
     */
    public function getFailureExceptionMessages()
    {
        return implode(
            ' ',
            array_map(
                function ($e) {
                    return $e['message'];
                },
                $this->failureExceptions
            )
        );
    }

    /**
     * @param string $message
     *
     * @return StepExecution
     */
    public function addError($message)
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors()
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
    public function addWarning($reason, array $reasonParameters, InvalidItemInterface $item)
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
     *
     * @return ArrayCollection
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Add row in summary
     *
     * @param string $key
     * @param mixed  $info
     */
    public function addSummaryInfo($key, $info)
    {
        $this->summary[$key] = $info;
    }

    /**
     * Increment counter in summary
     *
     * @param string  $key
     * @param integer $increment
     */
    public function incrementSummaryInfo($key, $increment = 1)
    {
        if (!isset($this->summary[$key])) {
            $this->summary[$key] = $increment;
        } else {
            $this->summary[$key] = $this->summary[$key] + $increment;
        }
    }

    /**
     * Get a summary row
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSummaryInfo($key)
    {
        return isset($this->summary[$key]) ? $this->summary[$key] : '';
    }

    /**
     * Set summary
     *
     * @param array $summary
     *
     * @return StepExecution
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return array
     */
    public function getSummary()
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
