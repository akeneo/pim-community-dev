<?php

declare(strict_types=1);

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
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);

        $productA->getId()->willReturn(42);
        $productB->getId()->willReturn(84);
        $productModelA->getId()->willReturn(42);
        $productModelB->getId()->willReturn(666);

        $runner->run($ruleDefinition1, [
            'selected_products' => ['product_42', 'product_84'],
            'selected_product_models' => ['product_model_42', 'product_model_666'],
        ])->shouldBeCalled();
        $runner->run($ruleDefinition2, [
            'selected_products' => ['product_42', 'product_84'],
            'selected_product_models' => ['product_model_42', 'product_model_666'],
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
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition]);

        $product->getId()->willReturn(42);
        $productModel->getId()->willReturn(null);

        $runner->run($ruleDefinition, [
            'selected_products' => ['product_42'],
            'selected_product_models' => [],
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
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition]);

        $product->getId()->willReturn(null);
        $productModel->getId()->willReturn(42);

        $runner->run($ruleDefinition, [
            'selected_products' => [],
            'selected_product_models' => ['product_model_42'],
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
        $ruleRepository->findAllOrderedByPriority()->willReturn([$ruleDefinition]);

        $product->getId()->willReturn(null);
        $productModel->getId()->willReturn(null);

        $this->write([$product, $productModel]);

        $runner->run(Argument::cetera())->shouldNotHaveBeenCalled();
    }
}
