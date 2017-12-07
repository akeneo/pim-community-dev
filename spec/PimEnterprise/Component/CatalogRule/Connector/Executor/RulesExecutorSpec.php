<?php

namespace spec\PimEnterprise\Component\CatalogRule\Connector\Executor;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Prophecy\Argument;

class RulesExecutorSpec extends ObjectBehavior
{
    function let(
        RunnerInterface $runner,
        RuleDefinitionRepositoryInterface $ruleRepository
    ) {
        $this->beConstructedWith($runner, $ruleRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Connector\Executor\RulesExecutor');
    }

    function it_should_implement_item_writer_interface()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_should_run_all_the_rules_for_products_only(
        $runner,
        $ruleRepository,
        ProductInterface $product,
        ProductModelInterface $productModel,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);
        $product->getId()->willReturn(42);
        $productModel->getId()->shouldNotBeCalled();
        $runner->run($ruleDefinition1, ['selected_products' => [42]])->shouldBeCalled();
        $runner->run($ruleDefinition2, ['selected_products' => [42]])->shouldBeCalled();
        $this->write([$product, $productModel]);
    }

    function it_should_not_run_any_rule_when_there_is_no_products(
        $runner,
        $ruleRepository,
        ProductInterface $product,
        ProductModelInterface $productModel,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);
        $product->getId()->willReturn(null);
        $productModel->getId()->shouldNotBeCalled();
        $runner->run(Argument::cetera())->shouldNotBeCalled();
        $this->write([$product, $productModel]);
    }
}
