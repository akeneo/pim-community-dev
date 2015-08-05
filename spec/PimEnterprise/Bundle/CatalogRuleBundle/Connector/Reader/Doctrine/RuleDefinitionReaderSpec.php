<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use PhpSpec\ObjectBehavior;

class RuleDefinitionReaderSpec extends ObjectBehavior
{
    function let(RuleDefinitionRepositoryInterface $ruleRepository)
    {
        $this->beConstructedWith($ruleRepository);
    }

    function it_implements()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
    }

    function it_reads_all_products(
        $ruleRepository,
        StepExecution $stepExecution,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $rulesDefinition = [$ruleDefinition1, $ruleDefinition2];
        $this->setStepExecution($stepExecution);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(2);

        $ruleRepository->findAll()->willReturn($rulesDefinition);

        $this->read()->shouldReturn([$ruleDefinition1, $ruleDefinition2]);
        $this->read()->shouldReturn(null);
    }
}
