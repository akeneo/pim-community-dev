<?php

namespace Pim\Bundle\BatchBundle\Job;

/**
 * Abstract implementation of the {@link Job} interface. Common dependencies
 * such as a {@link JobRepository}, {@link JobExecutionListener}s, and various
 * configuration parameters are set here. Therefore, common error handling and
 * listener calling activities are abstracted away from implementations.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.AbstractJob;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobRepository
{
    /**
     * Create a JobExecution object
     * @param string        $jobName       Name of the job
     * @param JobParameters $jobParameters Parameters for the execution of the job
     *
     * @return JobExecution
     */
    public function createJobExecution($jobName, JobParameters $jobParameters)
    {
        $ex = new JobExecution();
        $ex->setJobParameters($jobParameters);

        return $ex;
    }
}
