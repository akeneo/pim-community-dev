<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Prophecy\Argument;

class AddContextSubscriberSpec extends ObjectBehavior
{
    function let(
        VersionContext $versionContext,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $this->beConstructedWith($versionContext);

        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
    }

    function it_is_an_event_listener()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_before_job_execution_event()
    {
        $this->getSubscribedEvents()->shouldReturn([EventInterface::BEFORE_JOB_EXECUTION => 'addContext']);
    }

    function it_injects_versioning_context_into_the_version_manager($event, $jobInstance, $versionContext)
    {
        $jobInstance->getType()->willReturn(JobInstance::TYPE_IMPORT);
        $jobInstance->getCode()->willReturn('foo');

        $versionContext->addContextInfo('import "foo"')->shouldBeCalled();

        $this->addContext($event);
    }

    function it_does_not_inject_context_if_the_job_is_not_an_import($event, $jobInstance, $versionContext)
    {
        $jobInstance->getType()->willReturn(JobInstance::TYPE_EXPORT);

        $versionContext->addContextInfo(Argument::any())->shouldNotBeCalled();

        $this->addContext($event);
    }
}
