<?php

namespace Pim\Bundle\BatchBundle\Job;

use Pim\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Batch domain object representing a job. Job is an explicit abstraction
 * representing the configuration of a job specified by a developer.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.Job;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
