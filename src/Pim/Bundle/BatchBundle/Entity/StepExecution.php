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
 * @ORM\Table(name="pim_batch_step_execution")
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

    /**
     * @var JobExecution
     *
     * @ORM\ManyToOne(targetEntity="JobExecution", inversedBy="stepExecutions")
     * @ORM\JoinColumn(name="job_execution_id", referencedColumnName="id")
     */
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
     * @ORM\Column(name="filter_count", type="integer")
     */
    private $filterCount = 0;

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

    /* @var ExitStatus */
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
    private $terminateOnly = false;


    /**
     * @var array
     *
     * @ORM\Column(name="failure_exceptions", type="array", nullable=true)
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

        $this->failureExceptions = array();

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
     *
     * @return $this
     */
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;

        return $this;
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
     *
     * @return $this
     */
    public function setEndTime(\DateTime $endTime)
    {
        $this->endTime = $endTime;

        return $this;
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
     *
     * @return $this
     */
    public function setReadCount($readCount)
    {
        $this->readCount = $readCount;

        return $this;
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
     *
     * @return $this
     */
    public function setWriteCount($writeCount)
    {
        $this->writeCount = $writeCount;

        return $this;
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
     *
     * @return $this
     */
    public function setFilterCount($filterCount)
    {
        $this->filterCount = $filterCount;

        return $this;
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
     *
     * @return $this
     */
    public function setTerminateOnly()
    {
        $this->terminateOnly = true;

        return $this;
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
     *
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function upgradeStatus($status)
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
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
     *
     * @return $this
     */
    public function setExitStatus(ExitStatus $exitStatus)
    {
        $this->exitStatus = $exitStatus;
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();

        return $this;
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
     *
     * @return $this
     */
    public function addFailureException(\Exception $e)
    {
        $this->failureExceptions[] = $e;

        return $this;
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $summary = 'id=%d, name=[%s], status=[%s], exitCode=[%s], exitDescription=[%s], '.
            'readCount=%d, writeCount=%d, filterCount=%d';

        return sprintf(
            $summary,
            $this->id,
            $this->stepName,
            $this->status,
            $this->exitCode,
            $this->exitDescription,
            $this->readCount,
            $this->writeCount,
            $this->filterCount
        );
    }
}
