<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\StoppableJobInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class NullJobRepository implements JobRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createJobExecution(JobInterface $job, JobInstance $jobInstance, JobParameters $jobParameters): JobExecution
    {
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $jobExecution->setJobParameters($jobParameters);
        $jobExecution->setIsStoppable($job instanceof StoppableJobInterface && $job->isStoppable());

        return $jobExecution;
    }

    /**
     * {@inheritDoc}
     */
    public function updateJobExecution(JobExecution $jobExecution): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function updateStepExecution(StepExecution $stepExecution): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getLastJobExecution(JobInstance $jobInstance, $status): ?JobExecution
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $jobsExecutions): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function addWarning(Warning $warning): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function addWarnings(StepExecution $stepExecution, array $warnings): void
    {
    }
}
