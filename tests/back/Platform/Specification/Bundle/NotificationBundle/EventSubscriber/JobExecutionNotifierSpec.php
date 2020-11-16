<?php

namespace Specification\Akeneo\Platform\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Platform\Bundle\NotificationBundle\EventSubscriber\JobExecutionNotifier;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Prophecy\Argument;

class JobExecutionNotifierSpec extends ObjectBehavior
{
    function let(
        NotificationFactoryRegistry $factoryRegistry,
        NotifierInterface $notifier,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        ArrayCollection $warnings,
        JobInstance $jobInstance,
        BatchStatus $status
    ) {
        $this->beConstructedWith($factoryRegistry, $notifier);

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $jobParameters->has('user_to_notify')->willReturn(true);
        $jobParameters->get('user_to_notify')->willReturn('julia');

        $stepExecution->getWarnings()->willReturn($warnings);
        $jobExecution->getId()->willReturn(5);
        $jobInstance->getType()->willReturn('export');
        $jobInstance->getLabel()->willReturn('Product export');
        $event->getJobExecution()->willReturn($jobExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobExecutionNotifier::class);
    }

    function it_gives_the_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                'akeneo_batch.after_job_execution' => 'afterJobExecution'
            ]
        );
    }

    function it_does_not_notify_if_job_execution_parameters_has_no_job_parameters($event, $jobExecution, $notifier)
    {
        $jobExecution->getJobParameters()->willReturn(null);

        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_if_job_execution_parameters_has_no_user_to_notify(
        $event,
        $jobExecution,
        $notifier,
        JobParameters $jobParameters
    ) {
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('user_to_notify')->willReturn(false);

        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution(
        $event,
        $notifier,
        $factoryRegistry,
        $jobExecution,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory,
        ExitStatus $exitStatus
    ) {
        $factoryRegistry->get('export')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setMessage('pim_import_export.notification.export.success')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product export'])->willReturn($notification);
        $notification->setRoute('pim_importexport_export_execution_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'export'])->willReturn($notification);

        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);

        $notifier->notify($notification, ['julia'])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_a_mass_edit_job_execution(
        $event,
        $notifier,
        $jobInstance,
        $jobExecution,
        $factoryRegistry,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory,
        ExitStatus $exitStatus
    ) {
        $factoryRegistry->get('mass_edit')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setMessage('pim_mass_edit.notification.mass_edit.success')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product mass edit'])->willReturn($notification);
        $notification->setRoute('pim_enrich_job_tracker_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'mass_edit'])->willReturn($notification);

        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);

        $notifier->notify($notification, ['julia'])->shouldBeCalled();

        $jobInstance->getType()->willReturn('mass_edit');
        $jobInstance->getLabel()->willReturn('Product mass edit');
        $event->getJobExecution()->willReturn($jobExecution);

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_a_warning(
        $event,
        $warnings,
        $notifier,
        $jobExecution,
        $factoryRegistry,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory,
        ExitStatus $exitStatus
    ) {
        $factoryRegistry->get('export')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setType('warning')->willReturn($notification);
        $notification->setMessage('pim_import_export.notification.export.warning')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product export'])->willReturn($notification);
        $notification->setRoute('pim_importexport_export_execution_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'export'])->willReturn($notification);

        $notifier->notify($notification, ['julia'])->shouldBeCalled();

        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);

        $warnings->count()->willReturn(2);

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_an_error(
        $event,
        $status,
        $notifier,
        $jobExecution,
        $factoryRegistry,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory,
        ExitStatus $exitStatus
    ) {
        $status->isUnsuccessful()->willReturn(true);

        $factoryRegistry->get('export')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setType('warning')->willReturn($notification);
        $notification->setMessage('pim_import_export.notification.export.warning')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product export'])->willReturn($notification);
        $notification->setRoute('pim_importexport_export_execution_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'export'])->willReturn($notification);

        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);

        $notifier->notify($notification, ['julia'])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_of_the_completion_of_job_execution_which_has_been_stopped(
        $event,
        $status,
        $notifier,
        $jobExecution,
        $factoryRegistry,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory,
        ExitStatus $exitStatus
    ) {
        $status->isUnsuccessful()->willReturn(false);

        $factoryRegistry->get('export')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setType('success')->willReturn($notification);
        $notification->setMessage('pim_import_export.notification.export.success')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product export'])->willReturn($notification);
        $notification->setRoute('pim_importexport_export_execution_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'export'])->willReturn($notification);

        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::STOPPED);

        $notifier->notify($notification, ['julia'])->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_throws_an_exception_when_factory_is_not_found($event, $factoryRegistry, $jobExecution, ExitStatus $exitStatus)
    {
        $factoryRegistry->get('export')->willReturn(null);

        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);

        $this->shouldThrow(new \LogicException('No notification factory found for the "export" job type'))
            ->during('afterJobExecution', [$event]);
    }
}
