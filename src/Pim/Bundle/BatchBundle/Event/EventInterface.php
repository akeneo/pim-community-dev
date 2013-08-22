<?php

namespace Pim\Bundle\BatchBundle\Event;

/**
 * Interface of the batch bundle events
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventInterface
{
    /** Job execution events */
    const BEFORE_JOB_EXECUTION      = 'pim_batch.before_job_execution';
    const JOB_EXECUTION_STOPPED     = 'pim_batch.job_execution_stopped';
    const JOB_EXECUTION_INTERRUPTED = 'pim_batch.job_execution_interrupted';
    const JOB_EXECUTION_FATAL_ERROR = 'pim_batch.job_execution_fatal_error';
    const BEFORE_JOB_STATUS_UPGRADE = 'pim_batch.before_job_status_upgrade';
    const AFTER_JOB_EXECUTION       = 'pim_batch.after_job_execution';

    /** Step events */
    const BEFORE_STEP_EXECUTION     = 'pim_batch.before_step_execution';
}
