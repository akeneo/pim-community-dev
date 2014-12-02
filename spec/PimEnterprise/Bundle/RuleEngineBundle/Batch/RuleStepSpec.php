<?php

namespace spec\PimEnterprise\Bundle\RuleEngineBundle\Batch;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\RuleEngineBundle\Batch\RuleReaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleStepSpec extends ObjectBehavior
{
    public function let(RuleReaderInterface $reader, RunnerInterface $runner)
    {
        $this->beConstructedWith('step_name');
        $this->setReader($reader);
        $this->setRunner($runner);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Batch\RuleStep');
    }

    function it_is_a_batch_step()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Step\AbstractStep');
    }

    function it_gets_batch_configurable_step_elements($reader)
    {
        $this->getConfigurableStepElements()->shouldReturn(
            ['reader' => $reader]
        );
    }

    /**
    function it_executes(
        $reader,
        $runner,
        StepExecution $step,
        RuleInterface $rule,
        EventDispatcherInterface $dispatcher,
        JobRepositoryInterface $jobRepository,
        BatchStatus $status
    ) {
        $this->setEventDispatcher($dispatcher);
        $this->setJobRepository($jobRepository);

        $step->setStartTime(Argument::any())->shouldBeCalled();
        $step->setStatus(Argument::any())->shouldBeCalled();
        $step->getStatus()->willReturn($status);
        $step->isTerminateOnly()->willReturn(true);
        $step->upgradeStatus(Argument::any())->shouldBeCalled();

        $reader->setStepExecution($step)->shouldBeCalled();
        $reader->read()->shouldBeCalled()->willReturn($rule);
        $runner->run($rule)->shouldBeCalled();

        $this->execute($step);
    }
    */
}
