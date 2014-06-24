<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\ImportExportBundle\JobEvents;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

class JobProfileListenerSpec extends ObjectBehavior
{
    function let(SecurityContextInterface $securityContext, GenericEvent $event, JobInstance $job)
    {
        $event->getSubject()->willReturn($job);

        $this->beConstructedWith($securityContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\EventListener\JobProfileListener');
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            JobEvents::PRE_EDIT_JOB_PROFILE => 'checkEditPermission',
            JobEvents::PRE_EXECUTE_JOB_PROFILE => 'checkExecutePermission',
        ]);
    }

    function it_allows_to_edit_permission_when_job_edit_permission_is_granted($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EDIT_JOB_PROFILE, $job)->willReturn(true);

        $this->checkEditPermission($event);
    }

    function it_throws_access_denied_exception_when_no_edit_permission($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EDIT_JOB_PROFILE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkEditPermission', [$event]);
    }

    function it_allows_to_execute_permission_when_job_execution_permission_is_granted($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $job)->willReturn(true);

        $this->checkExecutePermission($event);
    }

    function it_throws_access_denied_exception_when_no_execute_permission($securityContext, $event, $job)
    {
        $securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $job)->willReturn(false);

        $this->shouldThrow(new AccessDeniedException())->during('checkExecutePermission', [$event]);
    }
}
