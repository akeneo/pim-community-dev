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
        /* @var JobExecution */
        $jobExecution = $this->jobRepository->createJobExecution($job->getName(), $jobParameters);

        $job->execute($jobExecution);

        return $jobExecution;
    }
}
