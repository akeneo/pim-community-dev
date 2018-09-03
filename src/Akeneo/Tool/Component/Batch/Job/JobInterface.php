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

    /**
     * @return string the name of this job
     */
    public function getName();

    /**
     * Run the {@link JobExecution} and update the meta information like status
     * and statistics as necessary. This method should not throw any exceptions
     * for failed execution. Clients should be careful to inspect the
     * {@link JobExecution} status to determine success or failure.
     *
     * @param JobExecution $execution a {@link JobExecution}
     */
    public function execute(JobExecution $execution);
}
