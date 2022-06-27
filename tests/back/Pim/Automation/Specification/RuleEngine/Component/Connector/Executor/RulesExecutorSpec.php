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
use Ramsey\Uuid\Uuid;

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

        $productA->getUuid()->willReturn(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'));
        $productA->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $productB->getUuid()->willReturn(Uuid::fromString('75cfd06e-9c03-44cb-93d3-b2e93d8f82b3'));
        $productB->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $productModelA->getId()->willReturn(42);
        $productModelB->getId()->willReturn(666);

        $runner->run($ruleDefinition1, [
            'selected_entities_with_values' => [
                'product_df470d52-7723-4890-85a0-e79be625e2ed',
                'product_75cfd06e-9c03-44cb-93d3-b2e93d8f82b3',
                'product_model_42',
                'product_model_666',
            ],
        ])->shouldBeCalled();
        $runner->run($ruleDefinition2, [
            'selected_entities_with_values' => [
                'product_df470d52-7723-4890-85a0-e79be625e2ed',
                'product_75cfd06e-9c03-44cb-93d3-b2e93d8f82b3',
                'product_model_42',
                'product_model_666'
            ],
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

        $product->getUuid()->willReturn(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'));
        $product->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $productModel->getId()->willReturn(null);

        $runner->run($ruleDefinition, [
            'selected_entities_with_values' => ['product_df470d52-7723-4890-85a0-e79be625e2ed'],
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

        $product->getUuid()->willReturn(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'));
        $product->getCreated()->willReturn(null);
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

        $product->getUuid()->willReturn(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'));
        $product->getCreated()->willReturn(null);
        $productModel->getId()->willReturn(null);

        $this->write([$product, $productModel]);

        $runner->run(Argument::cetera())->shouldNotHaveBeenCalled();
    }
}
