<?php

namespace Pim\Bundle\BatchBundle\Job\Launch;

use Pim\Bundle\BatchBundle\Job\JobInterface;
use Pim\Bundle\BatchBundle\Job\JobParameters;

/**
 * Simple interface for controlling jobs, including possible ad-hoc executions,
 * based on different runtime identifiers.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.launch.JobLauncher;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface JobLauncherInterface
{
    /**
     * Start a job execution for the given {@link Job} and {@link JobParameters}
     * . If a {@link JobExecution} was able to be created successfully, it will
     * always be returned by this method, regardless of whether or not the
     * execution was successful. If there is a past {@link JobExecution} which
     * has paused, the same {@link JobExecution} is returned instead of a new
     * one created. A exception will only be thrown if there is a failure to
     * start the job. If the job encounters some error while processing, the
     * JobExecution will be returned, and the status will need to be inspected.
     *
     * @param JobInterface  $job           The job to launch
     * @param JobParameters $jobParameters The job parameters
     *
     * @return the {@link JobExecution} if it returns synchronously. If the
     * implementation is asynchronous, the status might well be unknown.
     *
     * @throws JobExecutionAlreadyRunningException if the JobInstance identified
     * by the properties already has an execution running.
     * @throws IllegalArgumentException if the job or jobInstanceProperties are
     * null.
     * @throws JobRestartException if the job has been run before and
     * circumstances that preclude a re-start.
     * @throws JobInstanceAlreadyCompleteException if the job has been run
     * before with the same parameters and completed successfully
     * @throws JobParametersInvalidException if the parameters are not valid for
     * this job
     */
    public function run(JobInterface $job, JobParameters $jobParameters);
}
