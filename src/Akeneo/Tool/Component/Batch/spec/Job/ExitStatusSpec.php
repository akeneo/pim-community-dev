<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use PhpSpec\ObjectBehavior;

class ExitStatusSpec extends ObjectBehavior
{
    function it_sets_exit_code()
    {
        $this->beConstructedWith(ExitStatus::COMPLETED);
        $this->setExitCode(ExitStatus::STOPPED);
        $this->getExitCode()->shouldReturn(ExitStatus::STOPPED);
    }

    function it_sets_unknown_exit_code()
    {
        $this->beConstructedWith(ExitStatus::COMPLETED);
        $this->setExitCode(10);
        $this->getExitCode()->shouldReturn(ExitStatus::UNKNOWN);
    }

    function it_has_no_description()
    {
        $this->beConstructedWith("10");
        $this->getExitDescription()->shouldReturn('');
    }

    function it_sets_executing_exit_status()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $this->getExitCode()->shouldReturn('EXECUTING');
    }

    function it_compares_to_an_equal_status()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $otherStatus = new ExitStatus(ExitStatus::EXECUTING);

        $this->compareTo($otherStatus)->shouldReturn(0);
    }

    function it_compares_with_a_more_severe_status()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $otherStatus = new ExitStatus(ExitStatus::FAILED);

        $this->compareTo($otherStatus)->shouldReturn(-1);
    }

    function it_compares_with_a_less_severe_status()
    {
        $this->beConstructedWith(ExitStatus::COMPLETED);
        $otherStatus = new ExitStatus(ExitStatus::EXECUTING);

        $this->compareTo($otherStatus)->shouldReturn(1);
    }

    function it_does_logical_and_between_statuses_by_setting_bigger_severity_from_other_status()
    {
        $this->beConstructedWith(ExitStatus::COMPLETED);
        $otherStatus = new ExitStatus(ExitStatus::NOOP, 'my other desc');
        $this->logicalAnd($otherStatus);
        $this->getExitCode()->shouldReturn(ExitStatus::NOOP);
        $this->getExitDescription()->shouldReturn('my other desc');
    }

    function it_does_logical_and_between_statuses_by_setting_bigger_severity_from_main_status()
    {
        $this->beConstructedWith(ExitStatus::NOOP);
        $otherStatus = new ExitStatus(ExitStatus::COMPLETED, 'my other desc');
        $this->logicalAnd($otherStatus);
        $this->getExitCode()->shouldReturn(ExitStatus::NOOP);
        $this->getExitDescription()->shouldReturn('my other desc');
    }

    function it_adds_exit_code_to_the_same_status()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $otherStatus = new ExitStatus(ExitStatus::EXECUTING);
        $anotherStatus = new ExitStatus(ExitStatus::EXECUTING);
        $this->setExitCode($otherStatus->getExitCode());
        $this->getExitCode()->shouldReturn($otherStatus->getExitCode());
    }

    function it_adds_exit_description_with_stacktrace()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $exception = new \Exception("Foo");
        $this->addExitDescription($exception);
        $this->getExitDescription()->shouldReturn($exception->getTraceAsString());
    }

    function it_does_not_duplicates_descriptions()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $this->addExitDescription('Foo')->addExitDescription('Foo');
        $this->getExitDescription()->shouldReturn('Foo');
    }

    function it_adds_empty_description_to_existing_description()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $this->addExitDescription('Foo')->addExitDescription(null);
        $this->getExitDescription()->shouldReturn('Foo');
    }

    function it_changes_the_exit_code_without_changing_the_exit_description()
    {
        $this->beConstructedWith('BAR', 'Bar');
        $this->setExitCode('FOO');

        $this->getExitCode()->shouldReturn('FOO');
        $this->getExitDescription()->shouldReturn('Bar');
    }

    function it_adds_an_exit_description_to_an_existing_description()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $this->addExitDescription('Foo');
        $this->addExitDescription('Bar');

        $this->getExitDescription()->shouldReturn('Foo;Bar');
    }

    function it_is_running_when_status_is_unknown()
    {
        $this->beConstructedWith(ExitStatus::UNKNOWN);
        $this->isRunning()->shouldReturn(true);
    }

    function it_is_running_when_status_is_execution()
    {
        $this->beConstructedWith(ExitStatus::EXECUTING);
        $this->isRunning()->shouldReturn(true);
    }

    function it_is_displayable()
    {
        $this->beConstructedWith(ExitStatus::COMPLETED, 'My test description for completed status');
        $this->__toString()->shouldReturn('[COMPLETED] My test description for completed status');
    }
}
