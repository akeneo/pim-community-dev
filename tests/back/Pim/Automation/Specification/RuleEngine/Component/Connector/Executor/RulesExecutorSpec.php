<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Executor;

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Executor\RulesExecutor;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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
        $this->shouldHaveType(RulesExecutor::class);
    }

    function it_should_implement_item_writer_interface()
    {
        $this->shouldHaveType(ItemWriterInterface::class);
    }

    function it_should_run_all_the_rules_for_products_and_product_models(
        $runner,
        $ruleRepository,
        ProductInterface $productA,
        ProductInterface $productB,
        ProductModelInterface $productModelA,
        ProductModelInterface $productModelB,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $ruleRepository->findEnabledOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);

        $productA->getId()->willReturn(42);
        $productB->getId()->willReturn(84);
        $productModelA->getId()->willReturn(42);
        $productModelB->getId()->willReturn(666);

        $runner->run($ruleDefinition1, [
            'selected_entities_with_values' => ['product_42', 'product_84', 'product_model_42', 'product_model_666'],
        ])->shouldBeCalled();
        $runner->run($ruleDefinition2, [
            'selected_entities_with_values' => ['product_42', 'product_84', 'product_model_42', 'product_model_666'],
        ])->shouldBeCalled();

        $this->write([$productA, $productB, $productModelA, $productModelB]);
    }

    function it_should_run_all_the_rules_only_for_products(
        $runner,
        $ruleRepository,
        ProductInterface $product,
        ProductModelInterface $productModel,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleRepository->findEnabledOrderedByPriority()->willReturn([$ruleDefinition]);

        $product->getId()->willReturn(42);
        $productModel->getId()->willReturn(null);

        $runner->run($ruleDefinition, [
            'selected_entities_with_values' => ['product_42'],
        ])->shouldBeCalled();

        $this->write([$product, $productModel]);
    }

    function it_should_run_all_the_rules_only_for_product_models(
        $runner,
        $ruleRepository,
        ProductInterface $product,
        ProductModelInterface $productModel,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleRepository->findEnabledOrderedByPriority()->willReturn([$ruleDefinition]);

        $product->getId()->willReturn(null);
        $productModel->getId()->willReturn(42);

        $runner->run($ruleDefinition, [
            'selected_entities_with_values' => ['product_model_42'],
        ])->shouldBeCalled();

        $this->write([$product, $productModel]);
    }

    function it_should_not_run_any_rule_when_there_is_only_new_products_or_product_models(
        $runner,
        $ruleRepository,
        ProductInterface $product,
        ProductModelInterface $productModel,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleRepository->findEnabledOrderedByPriority()->willReturn([$ruleDefinition]);

        $product->getId()->willReturn(null);
        $productModel->getId()->willReturn(null);

        $this->write([$product, $productModel]);

        $runner->run(Argument::cetera())->shouldNotHaveBeenCalled();
    }
}
