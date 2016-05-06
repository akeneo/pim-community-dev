<?php

namespace spec\Akeneo\Component\Batch\Model;

use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;

class JobInstanceSpec extends ObjectBehavior
{
    function it_is_properly_instanciated()
    {
        $this->beConstructedWith('connector', 'type', 'alias');
        $this->getConnector()->shouldReturn('connector');
        $this->getType()->shouldReturn('type');
        $this->getAlias()->shouldReturn('alias');
    }

    function it_is_cloneable(JobExecution $jobExecution)
    {
        $this->addJobExecution($jobExecution);
        $clone = clone $this;
        $clone->shouldBeAnInstanceOf('Akeneo\Component\Batch\Model\JobInstance');
        $clone->getJobExecutions()->shouldHaveCount(1);
        $clone->getId()->shouldReturn(null);
    }

    function it_sets_the_job(Job $job)
    {
        $job->getConfiguration()->shouldBeCalled();
        $this->setJob($job);
    }

    function it_throws_logic_exception_when_changes_alias()
    {
        $this->beConstructedWith('connector', 'type', 'oldalias');
        $this->shouldThrow(
            new \LogicException('Alias already set in JobInstance')
        )->during(
            'setAlias',
            ['newalias']
        );
    }

    function it_throws_logic_exception_when_changes_connector()
    {
        $this->beConstructedWith('oldconnector', 'type', 'alias');
        $this->shouldThrow(
            new \LogicException('Connector already set in JobInstance')
        )->during(
            'setConnector',
            ['newconnector']
        );
    }
}
