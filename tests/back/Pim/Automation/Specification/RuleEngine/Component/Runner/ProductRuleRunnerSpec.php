<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Runner;

use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonRunnableException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Runner\ProductRuleRunner;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductRuleRunnerSpec extends ObjectBehavior
{
    function let(BuilderInterface $builder, SelectorInterface $selector, ApplierInterface $applier)
    {
        $this->beConstructedWith(
            $builder,
            $selector,
            $applier,
            ProductCondition::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRuleRunner::class);
    }

    function it_is_a_runner()
    {
        $this->shouldHaveType(RunnerInterface::class);
    }

    function it_supports_product_rule(RuleDefinitionInterface $definition1, RuleDefinitionInterface $definition2)
    {
        $definition1->getType()->willReturn('product');
        $definition2->getType()->willReturn('foo');

        $this->supports($definition1)->shouldReturn(true);
        $this->supports($definition2)->shouldReturn(false);
    }

    function it_runs_a_rule(
        BuilderInterface $builder,
        SelectorInterface $selector,
        ApplierInterface $applier,
        RuleSubjectSetInterface $subjectSet
    ) {
        $definition = new RuleDefinition();
        $rule = new Rule($definition);
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply($rule, $subjectSet)->shouldBeCalled();

        $this->run($definition);
    }

    function it_cannot_run_a_disabled_rule(
        BuilderInterface $builder
    ) {
        $definition = new RuleDefinition();
        $rule = new Rule($definition);
        $rule->setCode('foo');
        $rule->setEnabled(false);
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);

        $this->shouldThrow(new NonRunnableException('The "foo" rule is disabled.'))
            ->during('run', [$definition]);
    }

    function it_dries_run_a_rule(
        BuilderInterface $builder,
        SelectorInterface $selector,
        ApplierInterface $applier,
        RuleSubjectSetInterface $subjectSet
    ) {
        $definition = new RuleDefinition();
        $rule = new Rule($definition);
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply(Argument::any())->shouldNotBeCalled();

        $this->dryRun($definition);
    }

    function it_runs_a_rule_on_a_subset_of_products(
        BuilderInterface $builder,
        SelectorInterface $selector,
        ApplierInterface $applier,
        RuleDefinitionInterface $definition,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $rule->isEnabled()->willReturn(true);
        $rule->addCondition(Argument::any())->shouldBeCalled();
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply($rule, $subjectSet)->shouldBeCalled();

        $this->run($definition, ['selected_entities_with_values' => [1, 2, 3]]);
    }

    function it_dries_run_a_rule_on_a_subset_of_products(
        BuilderInterface $builder,
        SelectorInterface $selector,
        ApplierInterface $applier,
        RuleDefinitionInterface $definition,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $rule->isEnabled()->willReturn(true);
        $rule->addCondition(Argument::any())->shouldBeCalled();
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply(Argument::any())->shouldNotBeCalled();

        $this->dryRun($definition, ['selected_entities_with_values' => [1, 2, 3]]);
    }
}
