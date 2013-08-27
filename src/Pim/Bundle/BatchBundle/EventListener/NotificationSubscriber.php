<?php

namespace Pim\Bundle\BatchBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BatchBundle\Notification\Notifier;
use Pim\Bundle\BatchBundle\Event\JobExecutionEvent;

/**
 * Job execution notifier
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationSubscriber implements EventSubscriberInterface
{
    protected $notifiers = array();

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        );
    }

    /**
     * Register a notifier
     *
     * @param Notifier $notifier
     */
    public function registerNotifier(Notifier $notifier)
    {
        $this->notifiers[] = $notifier;
    }

    /**
     * Get the registered notifiers
     *
     * @return array
     */
    public function getNotifiers()
    {
        return $this->notifiers;
    }

    /**
     * Use the notifiers to notify
     *
     * @param JobExecutionEvent $jobExecutionEvent
     */
    public function afterJobExecution(JobExecutionEvent $jobExecutionEvent)
    {
        $jobExecution = $jobExecutionEvent->getJobExecution();

        foreach ($this->notifiers as $notifier) {
            $notifier->notify($jobExecution);
        }
    }
}
