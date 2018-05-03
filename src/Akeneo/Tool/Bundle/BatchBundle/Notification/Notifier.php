<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

/**
 * Interface of the job execution result notifiers
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface Notifier
{
    /**
     * Notify the user about the job execution
     *
     * @param JobExecution $jobExecution
     *
     * return null
     */
    public function notify(JobExecution $jobExecution);
}
