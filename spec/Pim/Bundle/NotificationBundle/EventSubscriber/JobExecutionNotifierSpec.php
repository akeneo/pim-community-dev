<?php

namespace spec\Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Job\ExitStatus;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;

class JobExecutionNotifierSpec extends ObjectBehavior
{
    function let(UserNotificationManager $manager, UserContext $context)
    {
        $this->beConstructedWith($manager, $context);
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
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        $manager
    ) {
        $event->getJobExecution()->willReturn($jobExecution);

        $jobExecution->getUser()->willReturn(null);
        $jobExecution->getStepExecutions()->shouldNotBeCalled();

        $manager->notify(Argument::cetera())->shouldNotBeCalled();
        $this->afterJobExecution($event)->shouldReturn(null);
    }

    function it_notifies_a_user_of_the_end_of_the_succeed_job_execution(
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        ArrayCollection $collection,
        JobInstance $jobInstance,
        User $user,
        ExitStatus $exitStatus,
        $manager
    ) {
        $event->getJobExecution()->willReturn($jobExecution);

        $jobExecution->getUser()->willReturn($user);
        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(5);

        $stepExecution->getWarnings()->willReturn($collection);
        $collection->count()->willReturn(0);

        $exitStatus->getExitCode()->willReturn(1);

        $jobInstance->getType()->willReturn('export');

        $manager
            ->notify(
                [$user],
                'pim_import_export.notification.export.complete',
                'success',
                [
                    'route' => 'pim_importexport_export_execution_show',
                    'routeParams' => [
                        'id' => 5
                    ]
                ]
            )
            ->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_end_of_the_job_execution_which_has_encountered_a_warning(
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        ArrayCollection $collection,
        JobInstance $jobInstance,
        ExitStatus $exitStatus,
        User $user,
        $manager
    ) {
        $event->getJobExecution()->willReturn($jobExecution);

        $jobExecution->getUser()->willReturn($user);
        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(5);

        $stepExecution->getWarnings()->willReturn($collection);
        $collection->count()->willReturn(2);

        $exitStatus->getExitCode()->willReturn(1);

        $jobInstance->getType()->willReturn('export');

        $manager
            ->notify(
                [$user],
                'pim_import_export.notification.export.complete',
                'warning',
                [
                    'route' => 'pim_importexport_export_execution_show',
                    'routeParams' => [
                        'id' => 5
                    ]
                ]
            )
            ->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_end_of_the_job_execution_which_has_encountered_an_error(
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        ArrayCollection $collection,
        JobInstance $jobInstance,
        User $user,
        ExitStatus $exitStatus,
        $manager
    ) {
        $event->getJobExecution()->willReturn($jobExecution);

        $jobExecution->getUser()->willReturn($user);
        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);
        $jobExecution->getExitStatus()->willReturn($exitStatus);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(5);

        $stepExecution->getWarnings()->willReturn($collection);
        $collection->count()->willReturn(2);

        $exitStatus->getExitCode()->willReturn(6);

        $jobInstance->getType()->willReturn('export');

        $manager
            ->notify(
                [$user],
                'pim_import_export.notification.export.complete',
                'error',
                [
                    'route' => 'pim_importexport_export_execution_show',
                    'routeParams' => [
                        'id' => 5
                    ]
                ]
            )
            ->shouldBeCalled();

        $this->afterJobExecution($event);
    }
}
