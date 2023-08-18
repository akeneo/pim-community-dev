<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Notification\Notifier;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PausedJobExecutionMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Job execution notifier
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
class RequeuePausedJobSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JobExecutionQueueInterface $jobExecutionQueue,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        ];
    }

    public function afterJobExecution(JobExecutionEvent $jobExecutionEvent): void
    {
        $jobExecution = $jobExecutionEvent->getJobExecution();
        if (!$jobExecution->getStatus()->isPaused()) {
            return;
        }

        $jobExecutionMessage = PausedJobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), []);
        try {
            $this->jobExecutionQueue->publish($jobExecutionMessage);
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred trying to publish paused job execution', [
                'job_execution_id' => $jobExecution->getId(),
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
