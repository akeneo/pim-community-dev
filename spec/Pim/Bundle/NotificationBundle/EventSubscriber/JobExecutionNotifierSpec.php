<?php

namespace spec\Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JobExecutionNotifierSpec extends ObjectBehavior
{
    function let(
        NotificationFactoryRegistry $factoryRegistry,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        ArrayCollection $warnings,
        JobInstance $jobInstance,
        UserInterface $user,
        BatchStatus $status,
        NotificationFactoryInterface $notificationFactory
    ) {
        $this->beConstructedWith(
            $factoryRegistry,
            $userNotifFactory,
            $userProvider,
            $notificationSaver,
            $userNotifsSaver
        );

        $jobExecution->getUser()->willReturn($user);
        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getWarnings()->willReturn($warnings);
        $jobExecution->getId()->willReturn(5);
        $jobInstance->getType()->willReturn('export');
        $jobInstance->getLabel()->willReturn('Product export');
        $event->getJobExecution()->willReturn($jobExecution);
        $factoryRegistry->get(Argument::any())->willReturn($notificationFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\EventSubscriber\JobExecutionNotifier');
    }

    function it_gives_the_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                'akeneo_batch.after_job_execution' => 'afterJobExecution'
            ]
        );
    }

    function it_does_not_notify_if_job_execution_has_no_user(
        $event,
        $jobExecution,
        $notificationSaver,
        $userNotifsSaver
    ) {
        $jobExecution->getUser()->willReturn(null);

        $jobExecution->getStatus()->shouldNotBeCalled();
        $notificationSaver->save()->shouldNotBeCalled();
        $userNotifsSaver->saveAll()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution(
        $event,
        $user,
        $userNotifFactory,
        $notificationSaver,
        $userNotifsSaver,
        $notificationFactory,
        $jobExecution,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification
    ) {
        $notificationFactory->create($jobExecution)->willReturn($notification);
        $notification->getMessage()->willReturn('pim_import_export.notification.export.success');
        $notification->getType()->willReturn('success');
        $notification->getRoute()->willReturn('pim_importexport_export_execution_show');
        $notification->getRouteParams()->willReturn(['id' => 5]);
        $notification->getMessageParams()->willReturn(['%label%' => 'Product export']);

        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);
        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_a_mass_edit_job_execution(
        $event,
        $user,
        $userNotifFactory,
        $notificationSaver,
        $userNotifsSaver,
        $notificationFactory,
        $jobInstance,
        $jobExecution,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification
    ) {
        $notificationFactory->create($jobExecution)->willReturn($notification);
        $notification->getMessage()->willReturn('pim_mass_edit.notification.mass_edit.success');
        $notification->getType()->willReturn('success');
        $notification->getRoute()->willReturn('pim_enrich_job_tracker_show');
        $notification->getRouteParams()->willReturn(['id' => 5]);
        $notification->getMessageParams()->willReturn(['%label%' => 'Product mass edit']);

        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);
        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $jobInstance->getType()->willReturn('mass_edit');
        $jobInstance->getLabel()->willReturn('Product mass edit');
        $event->getJobExecution()->willReturn($jobExecution);

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_a_warning(
        $event,
        $warnings,
        $user,
        $userNotifFactory,
        $notificationSaver,
        $userNotifsSaver,
        $notificationFactory,
        $jobExecution,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification
    ) {
        $notificationFactory->create($jobExecution)->willReturn($notification);
        $notification->getMessage()->willReturn('pim_import_export.notification.export.warning');
        $notification->getType()->willReturn('warning');
        $notification->getRoute()->willReturn('pim_importexport_export_execution_show');
        $notification->getRouteParams()->willReturn(['id' => 5]);
        $notification->getMessageParams()->willReturn(['%label%' => 'Product export']);

        $warnings->count()->willReturn(2);

        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);
        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_an_error(
        $event,
        $user,
        $userNotifFactory,
        $notificationSaver,
        $userNotifsSaver,
        $status,
        $notificationFactory,
        $jobExecution,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification
    ) {
        $notificationFactory->create($jobExecution)->willReturn($notification);
        $notification->getMessage()->willReturn('pim_import_export.notification.export.error');
        $notification->getType()->willReturn('error');
        $notification->getRoute()->willReturn('pim_importexport_export_execution_show');
        $notification->getRouteParams()->willReturn(['id' => 5]);
        $notification->getMessageParams()->willReturn(['%label%' => 'Product export']);

        $status->isUnsuccessful()->willReturn(true);

        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);
        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->afterJobExecution($event);
    }
}
