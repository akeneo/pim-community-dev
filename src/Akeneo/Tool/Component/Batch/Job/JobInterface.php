<?php

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

/**
 * Batch domain object representing a job. Job is an explicit abstraction
 * representing the configuration of a job specified by a developer.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.Job;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface JobInterface
{
    const WORKING_DIRECTORY_PARAMETER = 'working_directory';

    public function getName(): string;

    /**
     * Run the {@link JobExecution} and update the meta information like status
     * and statistics as necessary. This method should not throw any exceptions
     * for failed execution. Clients should be careful to inspect the
     * {@link JobExecution} status to determine success or failure.
     */
    public function execute(JobExecution $execution): void;

    /**
     * Public getter for the {@link JobRepositoryInterface} that is needed to manage the
     * state of the batch meta domain (jobs, steps, executions) during the life
     * of a job.
     */
    public function getJobRepository(): JobRepositoryInterface;
}
