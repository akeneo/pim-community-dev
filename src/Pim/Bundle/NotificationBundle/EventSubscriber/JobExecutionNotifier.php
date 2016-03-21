<?php

namespace Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

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

    /** @var UserNotificationFactory */
    protected $userNotifFactory;

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var SaverInterface */
    protected $notificationSaver;

    /** @var BulkSaverInterface */
    protected $userNotifsSaver;

    /**
     * @param NotificationFactoryRegistry $factoryRegistry
     * @param UserNotificationFactory     $userNotifFactory
     * @param UserProviderInterface       $userProvider
     * @param SaverInterface              $notificationSaver
     * @param BulkSaverInterface          $userNotifsSaver
     */
    public function __construct(
        NotificationFactoryRegistry $factoryRegistry,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver
    ) {
        $this->factoryRegistry   = $factoryRegistry;
        $this->userNotifFactory  = $userNotifFactory;
        $this->userProvider      = $userProvider;
        $this->notificationSaver = $notificationSaver;
        $this->userNotifsSaver   = $userNotifsSaver;
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
    public function afterJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $user         = $jobExecution->getUser();

        if (null === $user) {
            return;
        }

        $notification = $this->createNotification($jobExecution);
        $this->notify([$user], $notification);
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
        $type    = $jobExecution->getJobInstance()->getType();
        $factory = $this->factoryRegistry->get($type);

        if (null === $factory) {
            throw new \LogicException(sprintf('No notification factory found for the "%s" job type', $type));
        }

        $notification = $factory->create($jobExecution);

        return $notification;
    }

    /**
     * Send a user notification to given users
     *
     * @param array                 $users   Users which have to be notified (can be string or UserInterface[])
     * @param NotificationInterface $notification
     *
     * @return JobExecutionNotifier
     */
    protected function notify(array $users, NotificationInterface $notification)
    {
        $userNotifications = [];
        foreach ($users as $user) {
            $user = is_object($user) ? $user : $this->userProvider->loadUserByUsername($user);
            $userNotifications[] = $this->userNotifFactory->createUserNotification($notification, $user);
        }

        $this->notificationSaver->save($notification);
        $this->userNotifsSaver->saveAll($userNotifications);

        return $this;
    }
}
