<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

/**
 * Interface of the job execution result notifiers
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
interface Notifier
{
    public function notify(JobExecution $jobExecution): void;

    /**
     * @param array<string> $recipients
     */
    public function setRecipients(array $recipients): void;
}
