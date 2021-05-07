<?php

namespace Akeneo\Tool\Component\Batch\Event;

/**
 * Interface of the batch component events
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface EventInterface
{
    /** Job execution events */
    public const JOB_EXECUTION_CREATED = 'akeneo_batch.job_execution_created';
    public const BEFORE_JOB_EXECUTION = 'akeneo_batch.before_job_execution';
    public const JOB_EXECUTION_STOPPED = 'akeneo_batch.job_execution_stopped';
    public const JOB_EXECUTION_INTERRUPTED = 'akeneo_batch.job_execution_interrupted';
    public const JOB_EXECUTION_FATAL_ERROR = 'akeneo_batch.job_execution_fatal_error';
    public const BEFORE_JOB_STATUS_UPGRADE = 'akeneo_batch.before_job_status_upgrade';
    public const AFTER_JOB_EXECUTION = 'akeneo_batch.after_job_execution';

    /** Step execution events */
    public const BEFORE_STEP_EXECUTION = 'akeneo_batch.before_step_execution';
    public const STEP_EXECUTION_SUCCEEDED = 'akeneo_batch.step_execution_succeeded';
    public const STEP_EXECUTION_INTERRUPTED = 'akeneo_batch.step_execution_interrupted';
    public const STEP_EXECUTION_ERRORED = 'akeneo_batch.step_execution_errored';
    public const STEP_EXECUTION_COMPLETED = 'akeneo_batch.step_execution_completed';
    public const INVALID_ITEM = 'akeneo_batch.invalid_item';

    /** Item step events */
    public const ITEM_STEP_AFTER_BATCH = 'akeneo_batch.item_step_after_batch';
}
