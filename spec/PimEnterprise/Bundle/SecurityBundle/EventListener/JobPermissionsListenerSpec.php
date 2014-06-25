<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\ImportExportBundle\JobEvents;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

class JobPermissionsListenerSpec extends ObjectBehavior
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
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\EventListener\JobPermissionsListener');
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            JobEvents::PRE_EDIT_JOB_PROFILE       => 'checkEditPermission',
            JobEvents::PRE_EXECUTE_JOB_PROFILE    => 'checkExecutePermission',
            JobEvents::PRE_SHOW_JOB_PROFILE       => 'checkExecutePermission',
            JobEvents::PRE_SHOW_JOB_EXECUTION     => 'checkJobExecutionPermission',
            JobEvents::PRE_DL_FILES_JOB_EXECUTION => 'checkJobExecutionPermission',
            JobEvents::PRE_DL_LOG_JOB_EXECUTION   => 'checkJobExecutionPermission'
        ]);
    }

    function it_does_not_throw_exception_when_job_edit_permission_is_granted($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EDIT_JOB_PROFILE, $job)->willReturn(true);

        $this->checkEditPermission($event);
    }

    function it_throws_access_denied_exception_when_no_edit_permission($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EDIT_JOB_PROFILE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }

    function it_does_not_throw_exception_when_job_execute_permission_is_granted($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $job)->willReturn(true);

        $this->checkExecutePermission($event);
    }

    function it_throws_access_denied_exception_when_no_execute_permission($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkExecutePermission', [$event]);
    }

    function it_does_not_throw_exception_when_job_execute_permission_is_granted_on_job_execution(
        $securityContext,
        $event,
        $jobExecution,
        $job
    ) {
        $event->getSubject()->willReturn($jobExecution);
        $securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $job)->willReturn(true);

        $this->checkJobExecutionPermission($event);
    }

    function it_throws_access_denied_exception_when_job_execute_permission_is_granted_on_job_execution(
        $securityContext,
        $event,
        $jobExecution,
        $job
    ) {
        $event->getSubject()->willReturn($jobExecution);
        $securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkJobExecutionPermission', [$event]);
    }
}
