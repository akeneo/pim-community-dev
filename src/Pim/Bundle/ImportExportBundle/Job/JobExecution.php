<?php

namespace Pim\Bundle\ImportExportBundle;                                        

/**
 * 
 * Batch domain object representing the execution of a job
 *
 * Inspired by Spring Batch  org.springframework.batch.core.StepExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobExecution
{
    //private final JobParameters jobParameters;

//    private JobInstance jobInstance;
   
    // Collection of StepExecution
    /* @var ArrayCollection $stepExecutions */
    private $stepExecutions;

    /* @var BatchStatus $status */
    private $status;

    private $startTime = null;

    private $createTime = null;

    private $endTime = null;

    //private volatile Date lastUpdated = null;

    //private volatile ExitStatus exitStatus = ExitStatus.UNKNOWN;

    //private volatile ExecutionContext executionContext = new ExecutionContext();

    //private transient volatile List<Throwable> failureExceptions = new CopyOnWriteArrayList<Throwable>();

    public function __construct()
    {
        $this->batchStatus = new BatchStatus(BatchStatus::STARTING);
        $this->stepExecutions = new ArrayCollection();
        $this->createTime = now();
    }

    /**
     * Accessor for the step executions.
     *
     * @return ArrayCollection the step executions that were registered
     */
    public function getStepExecutions() {
        return $this->stepExecutions;
    }

    /**
     * Register a step execution with the current job execution.
     *
     * @param stepName the name of the step the new execution is associated with
     *
     * @return StepExecution the created stepExecution
     */
    public function createStepExecution($stepName) {
        $stepExecution = new StepExecution($stepName, $this);
        $this->stepExecutions->add($stepExecution);
        return $stepExecution;
    }

    /**
     * Test if this JobExecution indicates that it is running. It should
     * be noted that this does not necessarily mean that it has been persisted
     * as such yet.
     *
     * @return true if the end time is null
     */
    public function isRunning() {
        return $endTime == null;
    }

    /**
     * Test if this JobExecution indicates that it has been signalled to
     * stop.
     * @return true if the status is BatchStatus::STOPPING
     */
    public function isStopping() {
        return $status->getValue() == BatchStatus::STOPPING;
    }

    /**
     * Signal the JobExecution to stop. Iterates through the associated
     * StepExecution, calling StepExecution::setTerminateOnly().
     *
     */
    public function stop() {
        foreach ($stepExecutions as $stepExecution) {
            $stepExecution->setTerminateOnly();
        }
        $status = new BatchStatus(BatchStatus::STOPPING);
    }

    /*
     * @return the time when this execution was created.
     */
    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * @param createTime creation time of this execution.
     */
    public function setCreateTime($createTime) {
        $this->createTime = $createTime;
    }

    /**
     * Add a step executions to job's step execution
     *
     * @param stepExecution
     */
    public function addStepExecution(StepExecution $stepExecution) {
        $this->stepExecutions->add(stepExecution);
    }

    /**
     * Get the date representing the last time this JobExecution was updated in
     * the JobRepository.
     *
     * @return Date representing the last time this JobExecution was updated.
    public Date getLastUpdated() {
        return lastUpdated;
    }
     */

    /**
     * Set the last time this JobExecution was updated.
     *
     * @param lastUpdated
    public void setLastUpdated(Date lastUpdated) {
        this.lastUpdated = lastUpdated;
    }
     */

    public function __toString() {
        return sprintft(", startTime=%s, endTime=%s, lastUpdated=%s, status=%s, exitStatus=%s, job=[%s], jobParameters=[%s]",
                        $this->startTime,
                        $this->endTime,
                        $this->lastUpdated,
                        $this->status,
                        $this->exitStatus,
                        $this->jobInstance,
                        $this->jobParameters);
    }
}

