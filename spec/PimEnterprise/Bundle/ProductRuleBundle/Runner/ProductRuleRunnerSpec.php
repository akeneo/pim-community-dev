<?php

namespace spec\PimEnterprise\Bundle\ProductRuleBundle\Runner;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Prophecy\Argument;

class ProductRuleRunnerSpec extends ObjectBehavior
{
    function let(LoaderInterface $loader, SelectorInterface $selector, ApplierInterface $applier)
    {
        $this->beConstructedWith($loader, $selector, $applier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductRuleBundle\Runner\ProductRuleRunner');
    }

    function it_is_a_runner()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface');
    }

    function it_supports_product_rule(RuleInterface $rule1, RuleInterface $rule2)
    {
        $rule1->getType()->willReturn('product');
        $rule2->getType()->willReturn('foo');

        $this->supports($rule1)->shouldReturn(true);
        $this->supports($rule2)->shouldReturn(false);
    }

    function it_runs_a_rule(
        $loader,
        $selector,
        $applier,
        RuleInterface $rule,
        LoadedRuleInterface $loadedRule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $loader->load($rule)->shouldBeCalled()->willReturn($loadedRule);
        $selector->select($loadedRule)->shouldBeCalled()->willReturn($subjectSet);
        $applier->apply($loadedRule, $subjectSet)->shouldBeCalled();

        $this->run($rule);
    }
}
