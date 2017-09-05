<?php

namespace spec\Akeneo\Component\Batch\Model;

use Akeneo\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;

class StepExecutionSpec extends ObjectBehavior
{
    function let(JobExecution $jobExecution)
    {
        $this->beConstructedWith('myStepName', $jobExecution);
    }

    function it_is_properly_instanciated()
    {
        $this->getStatus()->shouldBeAnInstanceOf('Akeneo\Component\Batch\Job\BatchStatus');
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::STARTING);
        $this->getExitStatus()->shouldBeAnInstanceOf('Akeneo\Component\Batch\Job\ExitStatus');
        $this->getExitStatus()->getExitCode()->shouldReturn(ExitStatus::EXECUTING);
        $this->getExecutionContext()->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\ExecutionContext');
        $this->getWarnings()->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->getWarnings()->shouldBeEmpty();
        $this->getStartTime()->shouldBeAnInstanceOf('\Datetime');
        $this->getFailureExceptions()->shouldHaveCount(0);
    }

    function it_is_cloneable()
    {
        $clone = clone $this;
        $clone->shouldBeAnInstanceOf('Akeneo\Component\Batch\Model\StepExecution');
        $clone->getId()->shouldReturn(null);
    }

    function it_upgrades_status()
    {
        $this->getStatus()->shouldBeAnInstanceOf('Akeneo\Component\Batch\Job\BatchStatus');
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::STARTING);
        $this->upgradeStatus(BatchStatus::COMPLETED)->shouldBeAnInstanceOf('Akeneo\Component\Batch\Model\StepExecution');
        $this->getStatus()->shouldBeAnInstanceOf('Akeneo\Component\Batch\Job\BatchStatus');
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::COMPLETED);
    }

    function it_sets_exist_status(ExitStatus $exitStatus)
    {
        $this->setExitStatus($exitStatus)->shouldReturn($this);
    }

    function it_adds_a_failure_exception()
    {
        $exception = new \Exception('my msg');
        $this->addFailureException($exception)->shouldReturn($this);
        $this->getFailureExceptions()->shouldHaveCount(1);
    }

    function it_adds_warning(InvalidItemInterface $invalidItem)
    {
        $this->addWarning(
            'my reason',
            [],
            $invalidItem
        );
        $this->getWarnings()->shouldHaveCount(1);
    }

    function it_increments_summary_info()
    {
        $this->incrementSummaryInfo('counter');
        $this->getSummaryInfo('counter')->shouldReturn(1);
        $this->incrementSummaryInfo('counter', 3);
        $this->getSummaryInfo('counter')->shouldReturn(4);
    }

    function it_decrements_summary_info()
    {
        $this->decrementSummaryInfo('counter');
        $this->getSummaryInfo('counter')->shouldReturn(-1);
        $this->decrementSummaryInfo('counter', 3);
        $this->getSummaryInfo('counter')->shouldReturn(-4);
    }

    function it_is_displayable()
    {
        $this->__toString()->shouldReturn('id=0, name=[myStepName], status=[2], exitCode=[EXECUTING], exitDescription=[]');
    }
}
