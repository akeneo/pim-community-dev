<?php

namespace spec\Pim\Bundle\VersioningBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

class AddContextListenerSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $this->beConstructedWith($versionManager);

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

    function it_injects_versioning_context_into_the_version_manager($event, $jobInstance, $versionManager)
    {
        $jobInstance->getType()->willReturn(JobInstance::TYPE_IMPORT);
        $jobInstance->getCode()->willReturn('foo');

        $versionManager->setContext('import "foo"')->shouldBeCalled();

        $this->addContext($event);
    }

    function it_does_not_inject_context_if_the_job_is_not_an_import($event, $jobInstance, $versionManager)
    {
        $jobInstance->getType()->willReturn(JobInstance::TYPE_EXPORT);

        $versionManager->setContext(Argument::any())->shouldNotBeCalled();

        $this->addContext($event);
    }
}
