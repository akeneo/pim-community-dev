<?php

namespace Pim\Bundle\BatchBundle\Job\Launch;

use Pim\Bundle\BatchBundle\Job\JobInterface;
use Pim\Bundle\BatchBundle\Job\JobParameters;
use Pim\Bundle\BatchBundle\Job\JobRepository;

/**
 * Simple implementation of the {@link JobLauncher} interface.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.lauch.support.SimpleJobLauncher
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SimpleJobLauncher implements JobLauncherInterface
{
    /* @var JobRepository */
    private $jobRepository = null;

    /* @var TaskExecutor */
    private $taskExecutor;

    /**
     * Set the JobRepsitory.
     *
     * @param JobRepository $jobRepository
     */
    public function setJobRepository(JobRepository $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function run(JobInterface $job, JobParameters $jobParameters)
    {
        //assert.notNull(job, "The Job must not be null.");
        //assert.notNull(jobParameters, "The JobParameters must not be null.");

        /* @var JobExecution */
        $jobExecution = null;
        /*JobExecution lastExecution = jobRepository.getLastJobExecution(job.getName(), jobParameters);
        if (lastExecution != null) {
            if (!job.isRestartable()) {
                throw new JobRestartException("JobInstance already exists and is not restartable");
            }
            for (StepExecution execution : lastExecution.getStepExecutions()) {
                if (execution.getStatus() == BatchStatus.UNKNOWN) {
                    //throw
                    throw new JobRestartException("Step [" + execution.getStepName() + "] is of status UNKNOWN");
                }//end if
            }//end for
        }*/

        // Check the validity of the parameters before doing creating anything
        // in the repository...
        //job.getJobParametersValidator().validate(jobParameters);

        $jobExecution = $this->jobRepository->createJobExecution($job->getName(), $jobParameters);

        $job->execute($jobExecution);

        return $jobExecution;
    }
}
