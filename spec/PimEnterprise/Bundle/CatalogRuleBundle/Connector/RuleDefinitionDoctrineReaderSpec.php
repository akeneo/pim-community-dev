<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;

class RuleDefinitionDoctrineReaderSpec extends ObjectBehavior
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
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $rulesDefinition = [$ruleDefinition1, $ruleDefinition2];

        $ruleRepository->findAll()->shouldBeCalled()->willReturn($rulesDefinition);

        $this->read()->shouldReturn($rulesDefinition);
        $this->read()->shouldReturn(null);
    }
}
