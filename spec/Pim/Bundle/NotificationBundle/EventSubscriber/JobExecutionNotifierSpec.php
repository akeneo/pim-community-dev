<?php

namespace spec\Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Manager\NotificationManagerInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class JobExecutionNotifierSpec extends ObjectBehavior
{
    function let(
        NotificationManagerInterface $manager,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        ArrayCollection $warnings,
        JobInstance $jobInstance,
        UserInterface $user,
        BatchStatus $status
    ) {
        $this->beConstructedWith($manager);

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

    function it_does_not_notify_if_job_execution_has_no_user($event, $jobExecution, $manager)
    {
        $jobExecution->getUser()->willReturn(null);

        $jobExecution->getStatus()->shouldNotBeCalled();
        $manager->notify(Argument::cetera())->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution($event, $user, $manager)
    {
        $manager
            ->notify(
                [$user],
                'pim_import_export.notification.export.success',
                'success',
                [
                    'route' => 'pim_importexport_export_execution_show',
                    'routeParams' => ['id' => 5],
                    'messageParams' => ['%label%' => 'Product export']
                ]
            )
            ->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_a_mass_edit_job_execution($event, $user, $manager, $jobInstance, $jobExecution)
    {
        $manager
            ->notify(
                [$user],
                'pim_mass_edit.notification.mass_edit.success',
                'success',
                [
                    'route' => 'pim_enrich_job_tracker_show',
                    'routeParams' => ['id' => 5],
                    'messageParams' => ['%label%' => 'Product mass edit']
                ]
            )
            ->shouldBeCalled();

        $jobInstance->getType()->willReturn('mass_edit');
        $jobInstance->getLabel()->willReturn('Product mass edit');
        $event->getJobExecution()->willReturn($jobExecution);

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_a_warning(
        $event,
        $warnings,
        $user,
        $manager
    ) {
        $warnings->count()->willReturn(2);

        $manager
            ->notify(
                [$user],
                'pim_import_export.notification.export.warning',
                'warning',
                [
                    'route' => 'pim_importexport_export_execution_show',
                    'routeParams' => ['id' => 5],
                    'messageParams' => ['%label%' => 'Product export']
                ]
            )
            ->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_an_error(
        $event,
        $user,
        $status,
        $manager
    ) {
        $status->isUnsuccessful()->willReturn(true);

        $manager
            ->notify(
                [$user],
                'pim_import_export.notification.export.error',
                'error',
                [
                    'route' => 'pim_importexport_export_execution_show',
                    'routeParams' => ['id' => 5],
                    'messageParams' => ['%label%' => 'Product export']
                ]
            )
            ->shouldBeCalled();

        $this->afterJobExecution($event);
    }
}
