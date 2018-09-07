<?php

namespace spec\Akeneo\Pim\Permission\Bundle\EventSubscriber\Enrich;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class JobPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        GenericEvent $event,
        JobInstance $job,
        JobExecution $jobExecution
    ) {
        $this->beConstructedWith($authorizationChecker);

        $event->getSubject()->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($job);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\Permission\Bundle\EventSubscriber\Enrich\JobPermissionsSubscriber');
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

    function it_does_not_throw_exception_when_job_edit_permission_is_granted($authorizationChecker, $event, $job)
    {
        $authorizationChecker->isGranted(Attributes::EDIT, $job)->willReturn(true);

        $this->checkEditPermission($event);
    }

    function it_throws_access_denied_exception_when_no_edit_permission($authorizationChecker, $event, $job)
    {
        $authorizationChecker->isGranted(Attributes::EDIT, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }

    function it_does_not_throw_exception_when_job_execute_permission_is_granted($authorizationChecker, $event, $job)
    {
        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(true);

        $this->checkExecutePermission($event);
    }

    function it_throws_access_denied_exception_when_no_execute_permission($authorizationChecker, $event, $job)
    {
        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkExecutePermission', [$event]);
    }

    function it_does_not_throw_exception_when_a_job_permission_is_granted($authorizationChecker, $event, $job)
    {
        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $job)->willReturn(false);

        $this->checkShowPermission($event);

        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $job)->willReturn(true);

        $this->checkShowPermission($event);

        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $job)->willReturn(true);

        $this->checkShowPermission($event);
    }

    function it_throws_access_denied_exception_when_no_permission($authorizationChecker, $event, $job)
    {
        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkShowPermission', [$event]);
    }

    function it_does_not_throw_exception_when_job_execute_permission_is_granted_on_job_execution(
        $authorizationChecker,
        $event,
        $jobExecution,
        $job
    ) {
        $event->getSubject()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(true);

        $this->checkJobExecutionPermission($event);
    }

    function it_throws_access_denied_exception_when_job_execute_permission_is_granted_on_job_execution(
        $authorizationChecker,
        $event,
        $jobExecution,
        $job
    ) {
        $event->getSubject()->willReturn($jobExecution);
        $authorizationChecker->isGranted(Attributes::EXECUTE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkJobExecutionPermission', [$event]);
    }
}
