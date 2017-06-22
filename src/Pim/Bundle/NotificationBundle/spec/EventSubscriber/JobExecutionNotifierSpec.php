<?php

namespace spec\Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class JobExecutionNotifierSpec extends ObjectBehavior
{
    function let(
        NotificationFactoryRegistry $factoryRegistry,
        NotifierInterface $notifier,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        ArrayCollection $warnings,
        JobInstance $jobInstance,
        UserInterface $user,
        BatchStatus $status
    ) {
        $this->beConstructedWith($factoryRegistry, $notifier);

        $jobExecution->getUser()->willReturn($user);
        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);
        $jobExecution->getStatus()->willReturn($status);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getWarnings()->willReturn($warnings);
        $jobExecution->getId()->willReturn(5);
        $jobInstance->getType()->willReturn('export');
        $jobInstance->getLabel()->willReturn('Product export');
        $event->getJobExecution()->willReturn($jobExecution);
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

    function it_does_not_notify_if_job_execution_has_no_user($event, $jobExecution, $notifier)
    {
        $jobExecution->getUser()->willReturn(null);

        $jobExecution->getStatus()->shouldNotBeCalled();
        $notifier->notify(Argument::cetera())->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution(
        $event,
        $user,
        $notifier,
        $factoryRegistry,
        $jobExecution,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory
    ) {
        $factoryRegistry->get('export')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setMessage('pim_import_export.notification.export.success')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product export'])->willReturn($notification);
        $notification->setRoute('pim_importexport_export_execution_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'export'])->willReturn($notification);

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_a_mass_edit_job_execution(
        $event,
        $user,
        $notifier,
        $jobInstance,
        $jobExecution,
        $factoryRegistry,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory
    ) {
        $factoryRegistry->get('mass_edit')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setMessage('pim_mass_edit.notification.mass_edit.success')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product mass edit'])->willReturn($notification);
        $notification->setRoute('pim_enrich_job_tracker_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'mass_edit'])->willReturn($notification);

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $jobInstance->getType()->willReturn('mass_edit');
        $jobInstance->getLabel()->willReturn('Product mass edit');
        $event->getJobExecution()->willReturn($jobExecution);

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_a_warning(
        $event,
        $warnings,
        $user,
        $notifier,
        $jobExecution,
        $factoryRegistry,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory
    ) {
        $factoryRegistry->get('export')->willReturn($notificationFactory);
        $notificationFactory->create($jobExecution)->willReturn($notification);

        $notification->setType('warning')->willReturn($notification);
        $notification->setMessage('pim_import_export.notification.export.warning')->willReturn($notification);
        $notification->setMessageParams(['%label%' => 'Product export'])->willReturn($notification);
        $notification->setRoute('pim_importexport_export_execution_show')->willReturn($notification);
        $notification->setRouteParams(['id' => 5])->willReturn($notification);
        $notification->setContext(['actionType' => 'export'])->willReturn($notification);

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $warnings->count()->willReturn(2);

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_an_error(
        $event,
        $user,
        $status,
        $notifier,
        $jobExecution,
        $factoryRegistry,
        NotificationInterface $notification,
        NotificationFactoryInterface $notificationFactory
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

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_throws_an_exception_when_factory_is_not_found($event, $factoryRegistry)
    {
        $factoryRegistry->get('export')->willReturn(null);

        $this->shouldThrow(new \LogicException('No notification factory found for the "export" job type'))
            ->during('afterJobExecution', [$event]);
    }
}
