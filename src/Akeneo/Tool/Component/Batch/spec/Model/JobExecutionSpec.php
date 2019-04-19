<?php

namespace spec\Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class JobExecutionSpec extends ObjectBehavior
{
    function it_is_properly_instanciated()
    {
        $this->getStatus()->shouldBeAnInstanceOf(BatchStatus::class);
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::STARTING);
        $this->getExitStatus()->shouldBeAnInstanceOf(ExitStatus::class);
        $this->getExitStatus()->getExitCode()->shouldReturn(ExitStatus::UNKNOWN);
        $this->getExecutionContext()->shouldBeAnInstanceOf(ExecutionContext::class);
        $this->getStepExecutions()->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->getStepExecutions()->shouldBeEmpty();
        $this->getCreateTime()->shouldBeAnInstanceOf('\DateTime');
        $this->getFailureExceptions()->shouldHaveCount(0);
        $this->getRawParameters()->shouldHaveCount(0);
        $this->getJobParameters()->shouldBeNull();
        $this->getHealthCheckTime()->shouldBeNull();
    }

    function it_is_cloneable(
        ExecutionContext $executionContext,
        StepExecution $stepExecution1,
        StepExecution $stepExecution2
    ) {
        $this->setExecutionContext($executionContext);
        $this->addStepExecution($stepExecution1);
        $this->addStepExecution($stepExecution2);
        $clone = clone $this;
        $clone->shouldBeAnInstanceOf(JobExecution::class);
        $clone->getExecutionContext()->shouldBeAnInstanceOf(ExecutionContext::class);
        $clone->getStepExecutions()->shouldBeAnInstanceOf(ArrayCollection::class);
        $clone->getStepExecutions()->shouldHaveCount(2);
    }

    function it_upgrades_status()
    {
        $this->getStatus()->shouldBeAnInstanceOf(BatchStatus::class);
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::STARTING);
        $this->upgradeStatus(BatchStatus::COMPLETED)->shouldBeAnInstanceOf(JobExecution::class);
        $this->getStatus()->shouldBeAnInstanceOf(BatchStatus::class);
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::COMPLETED);
    }

    function it_sets_exist_status(ExitStatus $exitStatus)
    {
        $this->setExitStatus($exitStatus)->shouldReturn($this);
    }

    function it_creates_step_execution()
    {
        $newStep = $this->createStepExecution('myStepName');
        $newStep->shouldBeAnInstanceOf(StepExecution::class);
        $newStep->getStepName()->shouldReturn('myStepName');
    }

    function it_adds_step_execution(StepExecution $stepExecution1)
    {
        $this->getStepExecutions()->shouldHaveCount(0);
        $this->addStepExecution($stepExecution1);
        $this->getStepExecutions()->shouldHaveCount(1);
    }

    function it_indicates_if_running(BatchStatus $completedStatus)
    {
        $this->isRunning()->shouldReturn(true);
        $this->setStatus($completedStatus);
        $completedStatus->getValue()->willReturn(BatchStatus::COMPLETED);
        $this->isRunning()->shouldReturn(false);
    }

    function it_indicates_if_stopping(BatchStatus $stoppingStatus)
    {
        $this->isStopping()->shouldReturn(false);
        $stoppingStatus->getValue()->willReturn(BatchStatus::STOPPING);
        $this->setStatus($stoppingStatus);
        $this->isStopping()->shouldReturn(true);
    }

    function it_stops(StepExecution $stepExecution1)
    {
        $this->addStepExecution($stepExecution1);
        $stepExecution1->setTerminateOnly()->shouldBeCalled();
        $this->stop()->shouldBeAnInstanceOf(JobExecution::class);
    }

    function it_adds_a_failure_exception()
    {
        $exception = new \Exception('my msg');
        $this->addFailureException($exception)->shouldReturn($this);
        $this->getFailureExceptions()->shouldHaveCount(1);
    }

    function it_provides_aggregated_step_failure_exceptions(StepExecution $stepExecution1)
    {
        $stepExecution1->getFailureExceptions()->willReturn(['one structured exception']);
        $this->addStepExecution($stepExecution1);

        $this->getAllFailureExceptions()->shouldHaveCount(1);
    }

    function it_sets_job_instance(JobInstance $jobInstance)
    {
        $jobInstance->addJobExecution($this)->shouldBeCalled();
        $this->setJobInstance($jobInstance);
    }

    function it_provides_the_job_instance_label(JobInstance $jobInstance)
    {
        $this->setJobInstance($jobInstance);
        $jobInstance->getLabel()->willReturn('my label');
        $this->getLabel()->shouldReturn('my label');
    }

    function it_sets_raw_parameters_when_setting_job_parameters(JobParameters $jobParameters)
    {
        $jobParameters->all()->willReturn(['foo' => 'baz']);
        $this->setJobParameters($jobParameters);
        $this->getJobParameters()->shouldReturn($jobParameters);
        $this->getRawParameters()->shouldReturn(['foo' => 'baz']);
    }

    function it_sets_health_check_time()
    {
        $datetime = new \DateTime();
        $this->setHealthCheckTime($datetime);
        $this->getHealthCheckTime()->shouldReturn($datetime);
    }

    function it_is_displayable()
    {
        $this->__toString()->shouldReturn('startTime=, endTime=, updatedTime=, status=2, exitStatus=[UNKNOWN] , exitDescription=[], job=[]');
    }
}
