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

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
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
    public function createJobExecution(JobInstance $jobInstance, JobParameters $jobParameters)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function updateJobExecution(JobExecution $jobExecution)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getLastJobExecution(JobInstance $jobInstance, $status)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findPurgeables($days)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $jobsExecutions)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function addWarning(Warning $warning): void
    {
    }
}
