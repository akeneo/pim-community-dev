<?php

namespace Oro\Bundle\BatchBundle\Job;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Common interface for Job repositories which should handle how job are stored, updated
 * and retrieved
 *
 * Inspired by Spring Batch org.springframework.batch.core.repository.JobRepository;
 *
 */
interface JobRepositoryInterface
{
    /**
     * Create a JobExecution object
     * @param string        $jobName       Name of the job
     * @param JobParameters $jobParameters Parameters for the execution of the job
     *
     * @return JobExecution
     */
    public function createJobExecution(JobInstance $job);

    /**
     * Update a JobExecution object
     *
     * @param JobExecution
     */
    public function updateJobExecution(JobExecution $jobExecution);

    /**
     * Update a StepExecution object
     *
     * @return StepExecution
     */
    public function updateStepExecution(StepExecution $stepExecution);

    /**
     * Finalize all writes to the repository
     */
    public function flush();
}
