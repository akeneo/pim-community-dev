<?php

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;

/**
 * Common interface for Job repositories which should handle how job are stored, updated
 * and retrieved
 *
 * Inspired by Spring Batch org.springframework.batch.core.repository.JobRepository;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface JobRepositoryInterface
{
    /**
     * Create a JobExecution object
     *
     * @param JobInstance   $job
     * @param JobParameters $jobParameters
     *
     * @return JobExecution
     */
    public function createJobExecution(JobInstance $job, JobParameters $jobParameters);

    /**
     * Update a JobExecution
     *
     * @param JobExecution $jobExecution
     *
     * @return JobExecution
     */
    public function updateJobExecution(JobExecution $jobExecution);

    /**
     * Update a StepExecution
     *
     * @param StepExecution $stepExecution
     *
     * @return StepExecution
     */
    public function updateStepExecution(StepExecution $stepExecution);

    /**
     * Get the last job execution
     *
     * @param JobInstance $jobInstance
     * @param int         $status
     *
     * @return JobExecution|null
     */
    public function getLastJobExecution(JobInstance $jobInstance, $status);

    /**
     * Get purgeables jobs executions
     *
     * @param integer $days
     *
     * @return array
     */
    public function findPurgeables($days);

    /**
     * Remove jobs executions
     *
     * @param array $jobsExecutions
     */
    public function remove(array $jobsExecutions);

    public function addWarning(Warning $warning): void;
}
