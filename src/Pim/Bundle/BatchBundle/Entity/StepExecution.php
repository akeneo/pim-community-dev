<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\BatchBundle\Item\ExecutionContext;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

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
 * @ORM\Table(name="pim_step_execution")
 * @ORM\Entity()
 */
class StepExecution
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /* @var JobExecution $jobExecution */
    /* TODO: link with the jobExecution entity */
    private $jobExecution = null;

    /**
     * @var string
     *
     * @ORM\Column(name="step_name", type="string", length=100, nullable=true)
     * @Assert\NotBlank
     */
    private $stepName;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="read_count", type="integer")
     */
    private $readCount = 0;

    /**
     * @var integer
     *
     * @orm\column(name="write_count", type="integer")
     */
    private $writeCount = 0;

    /**
     * @var integer
     *
     * @orm\column(name="commit_count", type="integer")
     */
    private $commitCount = 0;

    /**
     * @var integer
     *
     * @orm\column(name="rollback_count", type="integer")
     */
    private $rollbackCount = 0;

    /**
     * @var integer
     *
     * @orm\column(name="read_skip_count", type="integer")
     */
    private $readSkipCount = 0;

    /**
     * @var integer
     *
     * @orm\column(name="process_skip_count", type="integer")
     */
    private $processSkipCount = 0;

    /**
     * @var integer
     *
     * @orm\column(name="write_skip_count", type="integer")
     */
    private $writeSkipCount = 0;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    private $endTime;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus $existStatus */
    private $exitStatus = null;

    /**
     * @var string
     *
     * @ORM\Column(name="exit_code", type="string", length=255, nullable=true)
     */
    private $exitCode = null;

    /**
     * @var string
     *
     * @ORM\Column(name="exit_description", type="text", nullable=true)
     */
    private $exitDescription = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="terminate_only", type="boolean", nullable=true)
     */
    private $terminateOnly;

    /**
     * @var integer
     *
     * @orm\column(name="filter_count", type="integer")
     */
    private $filterCount = 0;

    /* @var array */
    private $failureExceptionsObjects = null;

    /**
     * @var string
     *
     * @ORM\Column(name="failure_exceptions", type="text", nullable=true)
     */
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

        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::EXECUTING));

        $this->failureExceptionsObjects = array();

        $this->executionContext = new ExecutionContext();

        $this->startTime = new \DateTime();
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
    public function setEndTime(\DateTime $endTime)
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
    public function setStartTime(\DateTime $startTime)
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
        return new BatchStatus($this->status);
    }

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     */
    public function setStatus(BatchStatus $status)
    {
        $this->status = $status->getValue();
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
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);
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
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();
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
        return $this->failureExceptionsObjects;
    }

    /**
     * Add a failure exception
     * @param Exception $e
     */
    public function addFailureException(\Exception $e)
    {
        $this->failureExceptionsObjects[] = $e;
        $failureExceptions = array();
        foreach ($failureExceptions as $failureException) {
            $failureExceptions[] = array(
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            );
        }

        $this->failureExceptions = serialize($failureExceptions);
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $string = '';

        try {
            $string = $this->getSummary();
        } catch (\Exception $e) {
            $string = $e->getMessage();
        }

        return $string;
    }

    /**
     * Get summary
     * @return string
     */
    public function getSummary()
    {
        $summary = "id=%d, name=%s, status=%s, exitStatus=%s, readCount=%d, filterCount=%d"
            . ", writeCount=%d readSkipCount=%d, writeSkipCount=%d"
            . ", processSkipCount=%d, commitCount=%d, rollbackCount=%d";

        return sprintf(
            $summary,
            $this->id,
            $this->stepName,
            $this->status,
            $this->exitCode,
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
