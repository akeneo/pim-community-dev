<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Notification\Notifier;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Job execution notifier
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
class NotificationSubscriber implements EventSubscriberInterface
{
    private array $notifiers = [];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        ];
    }

    public function registerNotifier(Notifier $notifier): void
    {
        $this->notifiers[] = $notifier;
    }

    public function afterJobExecution(JobExecutionEvent $jobExecutionEvent): void
    {
        $jobExecution = $jobExecutionEvent->getJobExecution();

        foreach ($this->notifiers as $notifier) {
            $notifier->notify($jobExecution);
        }
    }
}
