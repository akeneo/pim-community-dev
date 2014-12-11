<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\RuleEngineBundle\Manager\RuleDefinitionManager;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Prophecy\Argument;

class RuleDefinitionWriterSpec extends ObjectBehavior
{
    public function let(
        RuleDefinitionManager $ruleDefManager
    ) {
        $this->beConstructedWith($ruleDefManager);
    }

    function it_implements()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_writes_a_rule_definition(
        RuleDefinition $rule1,
        RuleDefinition $rule2,
        StepExecution $stepExecution,
        RuleDefinitionManager $ruleDefManager
    ) {
        $rule1->getId()->willReturn(42);
        $items = [$rule1, $rule2];

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('update')->shouldBeCalledTimes(1);

        $this->setStepExecution($stepExecution);

        $ruleDefManager->saveAll($items)->shouldBeCalled();

        $this->write($items);
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }
}
