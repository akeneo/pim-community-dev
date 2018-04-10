<?php

namespace Akeneo\Component\Batch\Event;

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
    const BEFORE_JOB_EXECUTION = 'akeneo_batch.before_job_execution';
    const JOB_EXECUTION_STOPPED = 'akeneo_batch.job_execution_stopped';
    const JOB_EXECUTION_INTERRUPTED = 'akeneo_batch.job_execution_interrupted';
    const JOB_EXECUTION_FATAL_ERROR = 'akeneo_batch.job_execution_fatal_error';
    const BEFORE_JOB_STATUS_UPGRADE = 'akeneo_batch.before_job_status_upgrade';
    const AFTER_JOB_EXECUTION = 'akeneo_batch.after_job_execution';

    /** Step execution events */
    const BEFORE_STEP_EXECUTION = 'akeneo_batch.before_step_execution';
    const STEP_EXECUTION_SUCCEEDED = 'akeneo_batch.step_execution_succeeded';
    const STEP_EXECUTION_INTERRUPTED = 'akeneo_batch.step_execution_interrupted';
    const STEP_EXECUTION_ERRORED = 'akeneo_batch.step_execution_errored';
    const STEP_EXECUTION_COMPLETED = 'akeneo_batch.step_execution_completed';
    const INVALID_ITEM = 'akeneo_batch.invalid_item';

    /** Item step events */
    const ITEM_STEP_AFTER_BATCH = 'akeneo_batch.item_step_after_batch';
}
