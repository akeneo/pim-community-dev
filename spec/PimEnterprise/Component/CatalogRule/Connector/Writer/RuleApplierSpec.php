<?php

namespace spec\PimEnterprise\Component\CatalogRule\Connector\Writer;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;

class RuleApplierSpec extends ObjectBehavior
{
    function let(
        RunnerInterface $runner,
        RuleDefinitionRepositoryInterface $ruleRepository
    ) {
        $this->beConstructedWith($runner, $ruleRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Connector\Writer\RuleApplier');
    }

    function it_should_implement_item_writer_interface()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_should_run_all_the_rules(
        $runner,
        $ruleRepository,
        ProductInterface $product,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);
        $product->getId()->willReturn(42);
        $runner->run($ruleDefinition1, ['selected_products' => [42]])->shouldBeCalled();
        $runner->run($ruleDefinition2, ['selected_products' => [42]])->shouldBeCalled();
        $this->write([$product]);
    }

    function it_should_not_run_any_rule_when_there_is_no_products(
        $runner,
        $ruleRepository,
        ProductInterface $product,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);
        $product->getId()->willReturn(null);
        $runner->run(Argument::cetera())->shouldNotBeCalled();
        $this->write([$product]);
    }
}
