<?php

namespace spec\Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Step\StepInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('myname');
    }

    function it_provides_its_name()
    {
        $this->getName()->shouldReturn('myname');
    }

    function it_changes_its_name()
    {
        $this->setName('mynewname');
        $this->getName()->shouldReturn('mynewname');
    }

    function it_sets_event_dispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->setEventDispatcher($eventDispatcher)->shouldReturn($this);
    }

    function it_has_no_steps_by_default()
    {
        $this->getSteps()->shouldReturn([]);
    }

    function it_sets_steps(StepInterface $stepOne,StepInterface $stepTwo)
    {
        $this->setSteps([$stepOne, $stepTwo]);
        $this->getSteps()->shouldReturn([$stepOne, $stepTwo]);
    }

    function it_gets_a_step_by_its_name(StepInterface $stepOne,StepInterface $stepTwo)
    {
        $this->setSteps([$stepOne, $stepTwo]);
        $stepOne->getName()->willReturn('one');
        $stepTwo->getName()->willReturn('two');
        $this->getStep('two')->shouldReturn($stepTwo);
        $this->getStep('three')->shouldReturn(null);
    }

    function it_gets_step_names(StepInterface $stepOne,StepInterface $stepTwo)
    {
        $this->setSteps([$stepOne, $stepTwo]);
        $stepOne->getName()->willReturn('one');
        $stepTwo->getName()->willReturn('two');
        $this->getStepNames()->shouldReturn(['one', 'two']);
    }

    function it_adds_a_step(StepInterface $stepOne,StepInterface $stepTwo)
    {
        $this->setSteps([$stepOne]);
        $this->getSteps()->shouldReturn([$stepOne]);
        $this->addStep('two', $stepTwo);
        $this->getSteps()->shouldReturn([$stepOne, $stepTwo]);
    }

    function it_sets_a_job_repository(JobRepositoryInterface $jobRepository)
    {
        $this->getJobRepository()->shouldReturn(null);
        $this->setJobRepository($jobRepository);
        $this->getJobRepository()->shouldReturn($jobRepository);
    }

    function it_aggregates_the_steps_configuration(StepInterface $stepOne, StepInterface $stepTwo)
    {
        $this->setSteps([$stepOne, $stepTwo]);
        $stepOne->getConfiguration()->willReturn(['conf1A' => 'value1A', 'conf1B' => 'value1B']);
        $stepTwo->getConfiguration()->willReturn(['conf2' => 'value2']);

        $this->getConfiguration()->shouldReturn(['conf1A' => 'value1A', 'conf1B' => 'value1B', 'conf2' => 'value2']);
    }

    function it_injects_the_configuration_in_steps(StepInterface $stepOne, StepInterface $stepTwo)
    {
        $this->setSteps([$stepOne, $stepTwo]);
        $stepOne->setConfiguration(['conf1A' => 'value1A', 'conf2' => 'value2'])->shouldBeCalled();
        $stepTwo->setConfiguration(['conf1A' => 'value1A', 'conf2' => 'value2'])->shouldBeCalled();
        $this->setConfiguration(['conf1A' => 'value1A', 'conf2' => 'value2']);
    }

    function it_sets_show_and_edit_templates()
    {
        $this->setShowTemplate('my show tmpl');
        $this->getShowTemplate()->shouldReturn('my show tmpl');

        $this->setEditTemplate('my edit tmpl');
        $this->getEditTemplate()->shouldReturn('my edit tmpl');
    }

    function it_is_displayable()
    {
        $this->__toString()->shouldReturn('Akeneo\Component\Batch\Job\Job: [name=myname]');
    }

    function it_executes(
        JobExecution $jobExecution,
        JobRepositoryInterface $jobRepository,
        EventDispatcherInterface $dispatcher,
        BatchStatus $status
    ) {
        $this->setEventDispatcher($dispatcher);
        $this->setJobRepository($jobRepository);
        $jobExecution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::UNKNOWN);

        $dispatcher->dispatch(EventInterface::BEFORE_JOB_EXECUTION, Argument::any())->shouldBeCalled();
        $jobExecution->setStartTime(Argument::any())->shouldBeCalled();
        $jobExecution->setStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(EventInterface::AFTER_JOB_EXECUTION, Argument::any())->shouldBeCalled();
        $jobExecution->setEndTime(Argument::any())->shouldBeCalled();

        $this->execute($jobExecution);
    }
}
