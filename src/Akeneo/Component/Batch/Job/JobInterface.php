<?php

namespace Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Model\JobExecution;

/**
 * Batch domain object representing a job. Job is an explicit abstraction
 * representing the configuration of a job specified by a developer.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.Job;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @api
 */
interface JobInterface
{
    /**
     * @return string the name of this job
     *
     * @api
     */
    public function getName();

    /**
     * Run the {@link JobExecution} and update the meta information like status
     * and statistics as necessary. This method should not throw any exceptions
     * for failed execution. Clients should be careful to inspect the
     * {@link JobExecution} status to determine success or failure.
     *
     * @param JobExecution $execution a {@link JobExecution}
     *
     * @api
     */
    public function execute(JobExecution $execution);
}
