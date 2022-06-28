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
    public function createJobExecution(JobInterface $job, JobInstance $jobInstance, JobParameters $jobParameters): JobExecution;
    public function updateJobExecution(JobExecution $jobExecution): void;
    public function updateStepExecution(StepExecution $stepExecution): void;
    public function getLastJobExecution(JobInstance $jobInstance, int $status): ?JobExecution;

    /**
     * @param JobExecution[] $jobsExecutions
     */
    public function remove(array $jobsExecutions): void;

    public function addWarning(Warning $warning): void;

    /**
     * @param Warning[] $warnings
     */
    public function addWarnings(StepExecution $stepExecution, array $warnings): void;
}
