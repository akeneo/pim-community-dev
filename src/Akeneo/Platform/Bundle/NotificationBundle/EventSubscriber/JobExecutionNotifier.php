<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Job execution notifier
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNotifier implements EventSubscriberInterface
{
    public function __construct(
        private NotificationFactoryRegistry $factoryRegistry,
        private NotifierInterface $notifier,
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

    public function afterJobExecution(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $jobParameters = $jobExecution->getJobParameters();

        $usersToNotify = $this->getUsersToNotify($jobParameters);
        if (empty($usersToNotify)) {
            return;
        }

        if (ExitStatus::STOPPED === $jobExecution->getExitStatus()->getExitCode()) {
            return;
        }

        $notification = $this->createNotification($jobExecution);
        $this->notifier->notify($notification, $usersToNotify);
    }

    /**
     * @return array<string>
     */
    private function getUsersToNotify(?JobParameters $jobParameters): array
    {
        if (null === $jobParameters || !$jobParameters->has('users_to_notify')) {
            return [];
        }

        return $jobParameters->get('users_to_notify');
    }

    private function createNotification(JobExecution $jobExecution): NotificationInterface
    {
        $type = $jobExecution->getJobInstance()->getType();
        $factory = $this->factoryRegistry->get($type);

        if (null === $factory) {
            throw new \LogicException(sprintf('No notification factory found for the "%s" job type', $type));
        }

        return $factory->create($jobExecution);
    }
}
