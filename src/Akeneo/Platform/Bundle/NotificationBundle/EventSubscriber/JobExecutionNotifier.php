<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Job execution notifier
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNotifier implements EventSubscriberInterface
{
    /** @var NotificationFactoryRegistry */
    protected $factoryRegistry;

    /** @var NotifierInterface */
    protected $notifier;

    /**
     * @param NotificationFactoryRegistry $factoryRegistry
     * @param NotifierInterface           $notifier
     */
    public function __construct(
        NotificationFactoryRegistry $factoryRegistry,
        NotifierInterface $notifier
    ) {
        $this->factoryRegistry = $factoryRegistry;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        ];
    }

    /**
     * Notify a user of the end of the job
     *
     * @param JobExecutionEvent $event
     */
    public function afterJobExecution(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $jobParameters = $jobExecution->getJobParameters();

        if (null === $jobParameters || !$jobParameters->has('user_to_notify')) {
            return;
        }

        $user = $jobParameters->get('user_to_notify');
        if (null === $user) {
            return;
        }

        $notification = $this->createNotification($jobExecution);
        $this->notifier->notify($notification, [$user]);
    }

    /**
     * Retrieve the matching factory and create the notification
     *
     * @param JobExecution $jobExecution
     *
     * @throws \LogicException
     *
     * @return NotificationInterface
     */
    protected function createNotification(JobExecution $jobExecution)
    {
        $type = $jobExecution->getJobInstance()->getType();
        $factory = $this->factoryRegistry->get($type);

        if (null === $factory) {
            throw new \LogicException(sprintf('No notification factory found for the "%s" job type', $type));
        }

        $notification = $factory->create($jobExecution);

        return $notification;
    }
}
