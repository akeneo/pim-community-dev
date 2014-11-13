<?php

namespace spec\PimEnterprise\Bundle\RuleEngineBundle\Batch;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleRepositoryInterface;
use Prophecy\Argument;

class RuleReaderSpec extends ObjectBehavior
{
    function let(RuleRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Batch\RuleReader');
    }

    function it_is_rule_reader()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Batch\RuleReaderInterface');
    }

    function it_is_a_configurable_step_element()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
    }

    function it_gets_batch_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn(
            ['ruleCode' => []]
        );
    }

    function it_reads_a_rule($repository, RuleInterface $rule)
    {
        $this->setRuleCode('therule');
        $repository->findBy(['code' => 'therule'])->shouldBeCalled()->willReturn($rule);

        $this->read()->shouldReturn($rule);
    }
}
