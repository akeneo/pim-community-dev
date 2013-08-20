<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;
use Pim\Bundle\BatchBundle\Item\ExecutionContext;

/**
 * Batch domain object representing the execution of a job
 *
 * Inspired by Spring Batch  org.springframework.batch.job.JobExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_job_execution")
 * @ORM\Entity()
 */
class JobExecution
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
     *  @var array
     */
    private $stepExecutions;

    /**
     * @var Job
     * @ORM\ManyToOne(targetEntity="Job", inversedBy="jobExecutions")
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id")
     */
    private $job;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

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

    /**
     * @var DateTime
     *
     * @ORM\Column(name="create_time", type="datetime", nullable=true)
     */
    private $createTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_time", type="datetime", nullable=true)
     */
    private $updatedTime;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus $existStatus */
    private $exitStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="exit_code", type="string", length=255, nullable=true)
     */
    private $exitCode;

    /**
     * @var string
     *
     * @ORM\Column(name="exit_description", type="text", nullable=true)
     */
    private $exitDescription;

    /**
     * @var array
     *
     * @ORM\Column(name="failure_exceptions", type="text", nullable=true)
     */
    private $failureExceptions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::UNKNOWN));
        $this->stepExecutions = array();
        $this->createTime = new \DateTime();
        $this->failureExceptions = array();
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
     * @return JobExecution
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
     * @return JobExecution
     */
    public function setEndTime(\DateTime $endTime)
    {
        $this->endTime = $endTime;

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
     * @return JobExecution
     */
    public function setStartTime(\DateTime $startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }
    /**
     * Gets the time this execution has been created
     *
     * @return the time this execution has been created
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Sets the time this execution has been created
     *
     * @param mixed $createTime the time this execution has been created
     *
     * @return JobExecution
     */
    public function setCreateTime(\DateTime $createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Gets the time this execution has been updated
     *
     * @return the time this execution has been updated
     */
    public function getUpdatedTime()
    {
        return $this->updatedTime;
    }

    /**
     * Sets the time this execution has been updated
     *
     * @param mixed $updatedTime the time this execution has been updated
     *
     * @return JobExecution
     */
    public function setUpdatedTime(\DateTime $updatedTime)
    {
        $this->updatedTime = $updatedTime;

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
     * @return JobExecution
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
     * @return JobExecution
     */
    public function upgradeStatus($status)
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @param ExitStatus $exitStatus
     *
     * @return JobExecution
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
     * Accessor for the step executions.
     *
     * @return ArrayCollection the step executions that were registered
     */
    public function getStepExecutions()
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
    public function createStepExecution($stepName)
    {
        $stepExecution = new StepExecution($stepName, $this);

        return $stepExecution;
    }

    /**
     * Add a step executions to job's step execution
     *
     * @param StepExecution $stepExecution
     *
     * @return JobExecution
     */
    public function addStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecutions[] = $stepExecution;

        return $this;
    }

    /**
     * Test if this JobExecution indicates that it is running. It should
     * be noted that this does not necessarily mean that it has been persisted
     * as such yet.
     *
     * @return true if the end time is null
     */
    public function isRunning()
    {
        return ($this->startTime != null && $this->endTime == null);
    }

    /**
     * Test if this JobExecution indicates that it has been signalled to
     * stop.
     * @return true if the status is BatchStatus::STOPPING
     */
    public function isStopping()
    {
        return $this->status == BatchStatus::STOPPING;
    }

    /**
     * Signal the JobExecution to stop. Iterates through the associated
     * StepExecution, calling StepExecution::setTerminateOnly().
     *
     */
    public function stop()
    {
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
    public function getFailureExceptions()
    {
        return $this->failureExceptions;
    }

    /**
     * Add a failure exception
     * @param Exception $e
     *
     * @return JobExecution
     */
    public function addFailureException(\Exception $e)
    {
        $this->failureExceptions[] = $e;

        return $this;
    }

    /**
     * Return all failure causing exceptions for this JobExecution, including
     * step executions.
     *
     * @return array containing all exceptions causing failure for
     * this JobExecution.
     */
    public function getAllFailureExceptions()
    {
        $allExceptions = $this->failureExceptions;

        foreach ($this->stepExecutions as $stepExecution) {
            $allExceptions = array_merge($allExceptions, $stepExecution->getFailureExceptions());
        }

        return $allExceptions;
    }

    /**
     * Set the associated job
     *
     * @param Job $job The job to associate the JobExecution to
     *
     * @return JobExecution
     */
    public function setJob(Job $job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get the associated job
     *
     * @return $job The job to which the JobExecution is associated
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        $string = "";
        $startTime   = $this->startTime   != null ? $this ->startTime->format(\DateTime::ATOM)   : '';
        $endTime     = $this->endTime     != null ? $this ->endTime->format(\DateTime::ATOM)     : '';
        $updatedTime = $this->updatedTime != null ? $this ->updatedTime->format(\DateTime::ATOM) : '';
        $jobCode     = $this->job         != null ? $this->job->getCode()                        : '';

        $message = "startTime=%s, endTime=%s, updatedTime=%s, status=%s, "
            . "exitStatus=%s, job=%s";
        $string = sprintf(
            $message,
            $startTime,
            $endTime,
            $updatedTime,
            $this->status,
            $this->exitStatus,
            $jobCode
        );

        return $string;
    }
}
