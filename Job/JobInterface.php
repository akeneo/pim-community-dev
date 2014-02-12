<?php

namespace Akeneo\Bundle\BatchBundle\Job;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Batch domain object representing a job. Job is an explicit abstraction
 * representing the configuration of a job specified by a developer.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.Job;
 *
 */
interface JobInterface
{
    /**
     * @return the name of this job
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
