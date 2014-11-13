<?php

namespace spec\PimEnterprise\Bundle\ProductRuleBundle\Engine;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\ProductRuleBundle\Engine\ProductRuleApplier;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
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
        $this->shouldHaveType('PimEnterprise\Bundle\ProductRuleBundle\Engine\ProductRuleApplier');
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
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([$this->createSetActionArray()]);
        $subjectSet->getSubjects()->willReturn([]);

        $productUpdater->setValue([], 'field', 'value', 'locale', 'scope')->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_which_has_a_set_action_with_invalid_options(
        $eventDispatcher,
        LoadedRuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([
                $this->createSetActionArray() + ['invalid_option' => 'foo']
            ]);

        $this->shouldThrow('\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')
            ->during('apply', [$rule, $subjectSet]);
    }

    function it_applies_a_rule_which_has_a_copy_action(
        $eventDispatcher,
        $productUpdater,
        LoadedRuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([$this->createCopyActionArray()]);
        $subjectSet->getSubjects()->willReturn([]);

        $productUpdater
            ->copyValue([], 'from_field', 'to_field', 'from_locale', 'to_locale', 'from_scope', 'to_scope')
            ->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_which_has_a_copy_action_with_invalid_options(
        $eventDispatcher,
        LoadedRuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([
                $this->createCopyActionArray() + ['invalid_option' => 'foo']
            ]);

        $this->shouldThrow('\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')
            ->during('apply', [$rule, $subjectSet]);
    }

    function it_applies_a_rule_which_has_an_unknown_action(
        $eventDispatcher,
        LoadedRuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([['type' => 'foo']]);

        $this->shouldThrow(new \LogicException('The action "foo" is not supported yet.'))
            ->during('apply', [$rule, $subjectSet]);
    }

    private function createSetActionArray()
    {
        return [
            'type' => ProductRuleApplier::SET_ACTION,
            'field' => 'field',
            'value' => 'value',
            'locale' => 'locale',
            'scope' => 'scope',
        ];
    }

    private function createCopyActionArray()
    {
        return [
            'type' => ProductRuleApplier::COPY_ACTION,
            'from_field' => 'from_field',
            'to_field' => 'to_field',
            'from_locale' => 'from_locale',
            'to_locale' => 'to_locale',
            'from_scope' => 'from_scope',
            'to_scope' => 'to_scope',
        ];
    }
}
