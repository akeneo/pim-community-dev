<?php

namespace Pim\Bundle\Batch2Bundle\Event;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventInterface
{
    const BEFORE_JOB_EXECUTION = 'pim_batch.before_job_execution';
    const AFTER_JOB_EXECUTION  = 'pim_batch.after_job_execution';

    const BEFORE_STEP_EXECUTION = 'pim_batch.before_step_execution';
    const AFTER_STEP_EXECUTION  = 'pim_batch.after_step_execution';

    const AFTER_READ    = 'pim_batch.read';
    const AFTER_PROCESS = 'pim_batch.processed';
    const AFTER_WRITE   = 'pim_batch.written';
}
