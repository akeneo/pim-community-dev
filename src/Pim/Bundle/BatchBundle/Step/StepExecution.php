<?php

namespace Pim\Bundle\BatchBundle\Step;

use Pim\Bundle\BatchBundle\Job\JobExecution;

use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

use Pim\Bundle\BatchBundle\Item\ExecutionContext;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Batch domain object representation the execution of a step. Unlike
 * JobExecution, there are additional properties related the processing
 * of items such as commit count, etc.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.StepExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class StepExecution
{
    private $id;

    /* @var JobExecution $jobExecution */
    private $jobExecution = null;

    private $stepName;

    /* @var BatchStatus $status */
    private $status = null;

    private $readCount = 0;

    private $writeCount = 0;

    private $commitCount = 0;

    private $rollbackCount = 0;

    private $readSkipCount = 0;

    private $processSkipCount = 0;

    private $writeSkipCount = 0;

    private $startTime;

    private $endTime;

    private $lastUpdated;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus $existStatus */
    private $exitStatus = null;

    private $terminateOnly;

    private $filterCount = 0;

    /* @var ArrayCollection $failureExceptions */
    private $failureExceptions = null;

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

        $this->status = new BatchStatus(BatchStatus::STARTING);
        $this->exitStatus = new ExitStatus(ExitStatus::EXECUTING);

        $this->failureExceptions = new ArrayCollection();

        $this->executionContext = new ExecutionContext();

        $this->startTime = time();
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
     */
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;
    }

    /**
     * Returns the current number of commits for this execution
     *
     * @return the current number of commits
     */
    public function getCommitCount()
    {
        return $this->commitCount;
    }

    /**
     * Sets the current number of commits for this execution
     *
     * @param int $commitCount the current number of commits
     */
    public function setCommitCount($commitCount)
    {
        $this->commitCount = $commitCount;
    }

    /**
     * Returns the time that this execution ended
     *
     * @return the time that this execution ended
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Sets the time that this execution ended
     *
     * @param mixed $endTime the time that this execution ended
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Returns the current number of items read for this execution
     *
     * @return the current number of items read for this execution
     */
    public function getReadCount()
    {
        return $this->readCount;
    }

    /**
     * Sets the current number of read items for this execution
     *
     * @param integer $readCount the current number of read items for this execution
     */
    public function setReadCount($readCount)
    {
        $this->readCount = $readCount;
    }

    /**
     * Returns the current number of items written for this execution
     *
     * @return the current number of items written for this execution
     */
    public function getWriteCount()
    {
        return $this->writeCount;
    }

    /**
     * Sets the current number of written items for this execution
     *
     * @param integer $writeCount the current number of written items for this execution
     */
    public function setWriteCount($writeCount)
    {
        $this->writeCount = $writeCount;
    }

    /**
     * Returns the current number of rollbacks for this execution
     *
     * @return the current number of rollbacks for this execution
     */
    public function getRollbackCount()
    {
        return $this->rollbackCount;
    }

    /**
     * Returns the current number of items filtered out of this execution
     *
     * @return the current number of items filtered out of this execution
     */
    public function getFilterCount()
    {
        return $this->filterCount;
    }

    /**
     * Public setter for the number of items filtered out of this execution.
     * @param integer $filterCount the number of items filtered out of this execution to
     * set
     */
    public function setFilterCount($filterCount)
    {
        $this->filterCount = $filterCount;
    }

    /**
     * @return flag to indicate that an execution should halt
     */
    public function isTerminateOnly()
    {
        return $this->terminateOnly;
    }

    /**
     * Set a flag that will signal to an execution environment that this
     * execution (and its surrounding job) wishes to exit.
     */
    public function setTerminateOnly()
    {
        $this->terminateOnly = true;
    }

    /**
     * Setter for number of rollbacks for this execution
     * @param integer $rollbackCount
     */
    public function setRollbackCount($rollbackCount)
    {
        $this->rollbackCount = $rollbackCount;
    }

    /**
     * Gets the time this execution started
     *
     * @return the time this execution started
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Sets the time this execution started
     *
     * @param mixed $startTime the time this execution started
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * Returns the current status of this step
     *
     * @return BatchStatus the current status of this step
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     */
    public function setStatus(BatchStatus $status)
    {
        $this->$status = $status;
    }

    /**
     * Upgrade the status field if the provided value is greater than the
     * existing one. Clients using this method to set the status can be sure
     * that they don't overwrite a failed status with an successful one.
     *
     * @param mixed $status the new status value
     */
    public function upgradeStatus($status)
    {
        $this->status = $this->status->upgradeTo($status);
    }

    /**
     * @return the name of the step
     */
    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * @param ExitStatus $exitStatus
     */
    public function setExitStatus(ExitStatus $exitStatus)
    {
        $this->exitStatus = $exitStatus;
    }

    /**
     * @return the exitCode
     */
    public function getExitStatus()
    {
        return $this->exitStatus;
    }

    /**
     * Accessor for the execution context information of the enclosing job.
     *
     * @return the that was used to start this step execution.
     *
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
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
     * @param Exception $e
     */
    public function addFailureException(\Exception $e)
    {
        $this->failureExceptions->add($e);
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        return $this->getSummary();
    }

    /**
     * Get summary
     * @return string
     */
    public function getSummary()
    {
        $summary = "name=%s, status=%s, exitStatus=%s, readCount=%d, filterCount=%d"
            . ", writeCount=%d readSkipCount=%d, writeSkipCount=%d"
            . ", processSkipCount=%d, commitCount=%d, rollbackCount=%d";

        return sprintf(
            $summary,
            $this->stepName,
            $this->status->getValue(),
            $this->exitStatus->getExitCode(),
            $this->readCount,
            $this->filterCount,
            $this->writeCount,
            $this->readSkipCount,
            $this->writeSkipCount,
            $this->processSkipCount,
            $this->commitCount,
            $this->rollbackCount
        );
    }
}
