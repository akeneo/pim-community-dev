<?php

namespace Pim\Bundle\BatchBundle\Notification;

use Pim\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Interface of the job execution result notifiers
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
