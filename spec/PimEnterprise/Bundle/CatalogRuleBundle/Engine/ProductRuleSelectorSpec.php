<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Doctrine\ORM\Query;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductRuleSelectorSpec extends ObjectBehavior
{
    public function let(
        ProductQueryFactoryInterface $productQueryFactory,
        ProductRepositoryInterface $repo,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $productQueryFactory,
            $repo,
            $eventDispatcher,
            'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSet'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector');
    }

    public function it_should_be_a_selector()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface');
    }

    public function it_supports_a_product_rule(
        RuleInterface $ruleOK,
        RuleInterface $ruleKO
    ) {
        $ruleOK->getType()->willReturn('product');
        $ruleKO->getType()->willReturn('foo');

        $this->supports($ruleOK)->shouldReturn(true);
        $this->supports($ruleKO)->shouldReturn(false);
    }

    public function it_selects_subjects_of_a_rule(
        $eventDispatcher,
        $productQueryFactory,
        ProductQueryBuilderInterface $pqb,
        RuleInterface $rule,
        ProductConditionInterface $condition
    ) {
        $pqb->execute()->willReturn([]);
        $rule->getConditions()->willReturn([$condition]);
        $rule->getCode()->willReturn('therule');
        $condition->getField()->willReturn('field');
        $condition->getOperator()->willReturn('operator');
        $condition->getValue()->willReturn('value');

        $productQueryFactory->create()->shouldBeCalled()->willReturn($pqb);
        $pqb->addFilter('field', 'operator', 'value')->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::PRE_SELECT, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_SELECT, Argument::any())->shouldBeCalled();

        $this->select($rule)->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSet');
    }

    public function it_selects_subject_of_a_rule_that_has_conditions(
        $eventDispatcher,
        $productQueryFactory,
        ProductQueryBuilderInterface $pqb,
        RuleInterface $rule,
        ProductConditionInterface $condition
    ) {
        $pqb->execute()->willReturn([]);
        $rule->getConditions()->willReturn([$condition]);
        $rule->getCode()->willReturn('therule');
        $condition->getField()->willReturn('field');
        $condition->getOperator()->willReturn('operator');
        $condition->getValue()->willReturn('value');

        $productQueryFactory->create()->shouldBeCalled()->willReturn($pqb);
        $eventDispatcher->dispatch(RuleEvents::PRE_SELECT, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_SELECT, Argument::any())->shouldBeCalled();
        $pqb->addFilter('field', 'operator', 'value')->shouldBeCalled();

        $this->select($rule)->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSet');
    }
}
