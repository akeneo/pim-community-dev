<?php

namespace Pim\Bundle\BatchBundle\Job;

use Pim\Bundle\BatchBundle\Step\StepInterface;

/**
 * Strategy interface for handling a {@link Step} on behalf of a {@link Job}.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.job.StepHandler;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface StepHandlerInterface
{
    /**
     * Handle a step and return the execution for it. Does not save the
     * {@link JobExecution}, but should manage the persistence of the
     * {@link StepExecution} if required (e.g. at least it needs to be added to
     * a repository before the step can eb executed).
     *
     * @param StepInterface $step         a {@link Step}
     * @param JobExecution  $jobExecution a {@link JobExecution}
     *
     * @return an execution of the step
     *
     * @throws JobInterruptedException if there is an interruption
     * @throws JobRestartException     if there is a problem restarting a failed
     * step
     * @throws StartLimitExceededException if the step exceeds its start limit
     *
     * @see Job#execute(JobExecution)
     * @see Step#execute(StepExecution)
     */
    public function handleStep(StepInterface $step, JobExecution $jobExecution);
}
