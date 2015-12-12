<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class RuleDefinitionWriterSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $saver
    ) {
        $this->beConstructedWith($saver);
    }

    function it_implements()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\ItemWriterInterface');
        $this->shouldHaveType('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_writes_a_rule_definition(
        $saver,
        RuleDefinition $rule1,
        RuleDefinition $rule2,
        StepExecution $stepExecution
    ) {
        $rule1->getId()->willReturn(42);
        $items = [$rule1, $rule2];

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(1);

        $this->setStepExecution($stepExecution);

        $saver->saveAll($items)->shouldBeCalled();

        $this->write($items);
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }
}
