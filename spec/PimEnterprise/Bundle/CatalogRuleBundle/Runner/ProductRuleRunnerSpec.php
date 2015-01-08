<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Runner;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Prophecy\Argument;

class ProductRuleRunnerSpec extends ObjectBehavior
{
    public function let(BuilderInterface $builder, SelectorInterface $selector, ApplierInterface $applier)
    {
        $this->beConstructedWith(
            $builder,
            $selector,
            $applier,
            'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Runner\ProductRuleRunner');
    }

    public function it_is_a_runner()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface');
    }

    public function it_supports_product_rule(RuleDefinitionInterface $definition1, RuleDefinitionInterface $definition2)
    {
        $definition1->getType()->willReturn('product');
        $definition2->getType()->willReturn('foo');

        $this->supports($definition1)->shouldReturn(true);
        $this->supports($definition2)->shouldReturn(false);
    }

    public function it_runs_a_rule(
        $builder,
        $selector,
        $applier,
        RuleDefinitionInterface $definition,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply($rule, $subjectSet)->shouldBeCalled();

        $this->run($definition);
    }

    public function it_dries_run_a_rule(
        $builder,
        $selector,
        $applier,
        RuleDefinitionInterface $definition,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply(Argument::any())->shouldNotBeCalled();

        $this->dryRun($definition);
    }

    public function it_runs_a_rule_on_a_subset_of_products(
        $builder,
        $selector,
        $applier,
        RuleDefinitionInterface $definition,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $rule->addCondition(Argument::any())->shouldBeCalled();
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply($rule, $subjectSet)->shouldBeCalled();

        $this->run($definition, ['selected_products' => [1, 2, 3]]);
    }

    public function it_dries_run_a_rule_on_a_subset_of_products(
        $builder,
        $selector,
        $applier,
        RuleDefinitionInterface $definition,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $builder->build($definition)->shouldBeCalled()->willReturn($rule);
        $rule->addCondition(Argument::any())->shouldBeCalled();
        $selector->select($rule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply(Argument::any())->shouldNotBeCalled();

        $this->dryRun($definition, ['selected_products' => [1, 2, 3]]);
    }
}
