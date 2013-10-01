<?php

namespace Oro\Bundle\BatchBundle\Notification;

use Oro\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Interface of the job execution result notifiers
 *
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
