<?php

namespace Akeneo\Bundle\BatchBundle\Notification;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

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
