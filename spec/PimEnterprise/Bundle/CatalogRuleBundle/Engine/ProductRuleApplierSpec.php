<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductRuleApplierSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher, ProductUpdaterInterface $productUpdater)
    {
        $this->beConstructedWith($productUpdater, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier');
    }

    function it_is_a_rule_applier()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface');
    }

    function it_supports_a_product_rule(
        LoadedRuleInterface $ruleOK,
        LoadedRuleInterface $ruleKO
    ) {
        $ruleOK->getType()->willReturn('product');
        $ruleKO->getType()->willReturn('foo');

        $this->supports($ruleOK)->shouldReturn(true);
        $this->supports($ruleKO)->shouldReturn(false);
    }

    function it_applies_a_rule($eventDispatcher, LoadedRuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([]);

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_which_has_a_set_action(
        $eventDispatcher,
        $productUpdater,
        LoadedRuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductSetValueActionInterface $action
    ) {
        $action->getField()->willReturn('sku');
        $action->getValue()->willReturn('foo');
        $action->getScope()->willReturn('ecommerce');
        $action->getLocale()->willReturn('en_US');
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([$action]);
        $subjectSet->getSubjects()->willReturn([]);

        $productUpdater->setValue([], 'sku', 'foo', 'en_US', 'ecommerce')->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_which_has_a_copy_action(
        $eventDispatcher,
        $productUpdater,
        LoadedRuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductCopyValueAction $action
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('description');
        $action->getFromLocale()->willReturn('fr_FR');
        $action->getToLocale()->willReturn('fr_CH');
        $action->getFromScope()->willReturn('ecommerce');
        $action->getToScope()->willReturn('tablet');
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([$action]);
        $subjectSet->getSubjects()->willReturn([]);

        $productUpdater
            ->copyValue([], 'sku', 'description', 'fr_FR', 'fr_CH', 'ecommerce', 'tablet')
            ->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_which_has_an_unknown_action(
        $eventDispatcher,
        LoadedRuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([new \stdClass()]);

        $this->shouldThrow(new \LogicException('The action "stdClass" is not supported yet.'))
            ->during('apply', [$rule, $subjectSet]);
    }
}
