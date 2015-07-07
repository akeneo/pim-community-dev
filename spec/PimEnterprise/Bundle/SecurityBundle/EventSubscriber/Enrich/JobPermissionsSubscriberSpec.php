<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class JobPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        SecurityContextInterface $securityContext,
        GenericEvent $event,
        JobInstance $job,
        JobExecution $jobExecution
    ) {
        $this->beConstructedWith($securityContext);

        $event->getSubject()->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($job);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich\JobPermissionsSubscriber');
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            JobProfileEvents::PRE_EDIT             => 'checkEditPermission',
            JobProfileEvents::PRE_REMOVE           => 'checkEditPermission',
            JobProfileEvents::PRE_EXECUTE          => 'checkExecutePermission',
            JobProfileEvents::PRE_SHOW             => 'checkShowPermission',
            JobExecutionEvents::PRE_SHOW           => 'checkJobExecutionPermission',
            JobExecutionEvents::PRE_DOWNLOAD_FILES => 'checkJobExecutionPermission',
            JobExecutionEvents::PRE_DOWNLOAD_LOG   => 'checkJobExecutionPermission'
        ]);
    }

    function it_does_not_throw_exception_when_job_edit_permission_is_granted($securityContext, $event, $job)
    {
        $securityContext->isGranted(Attributes::EDIT, $job)->willReturn(true);

        $this->checkEditPermission($event);
    }

    function it_throws_access_denied_exception_when_no_edit_permission($securityContext, $event, $job)
    {
        $securityContext->isGranted(Attributes::EDIT, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }

    function it_does_not_throw_exception_when_job_execute_permission_is_granted($securityContext, $event, $job)
    {
        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(true);

        $this->checkExecutePermission($event);
    }

    function it_throws_access_denied_exception_when_no_execute_permission($securityContext, $event, $job)
    {
        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkExecutePermission', [$event]);
    }

    function it_does_not_throw_exception_when_a_job_permission_is_granted($securityContext, $event, $job)
    {
        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(true);
        $securityContext->isGranted(Attributes::EDIT, $job)->willReturn(false);

        $this->checkShowPermission($event);

        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(false);
        $securityContext->isGranted(Attributes::EDIT, $job)->willReturn(true);

        $this->checkShowPermission($event);

        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(true);
        $securityContext->isGranted(Attributes::EDIT, $job)->willReturn(true);

        $this->checkShowPermission($event);
    }

    function it_throws_access_denied_exception_when_no_permission($securityContext, $event, $job)
    {
        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(false);
        $securityContext->isGranted(Attributes::EDIT, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkShowPermission', [$event]);
    }

    function it_does_not_throw_exception_when_job_execute_permission_is_granted_on_job_execution(
        $securityContext,
        $event,
        $jobExecution,
        $job
    ) {
        $event->getSubject()->willReturn($jobExecution);
        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(true);

        $this->checkJobExecutionPermission($event);
    }

    function it_throws_access_denied_exception_when_job_execute_permission_is_granted_on_job_execution(
        $securityContext,
        $event,
        $jobExecution,
        $job
    ) {
        $event->getSubject()->willReturn($jobExecution);
        $securityContext->isGranted(Attributes::EXECUTE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkJobExecutionPermission', [$event]);
    }
}
