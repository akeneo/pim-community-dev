<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductRuleSelectorSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $queryBuilderFactory,
        ProductRepositoryInterface $repo,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $queryBuilderFactory,
            $repo,
            $eventDispatcher,
            'Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSet'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector');
    }

    function it_should_be_a_selector()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Engine\SelectorInterface');
    }

    function it_selects_subjects_of_a_rule(
        $eventDispatcher,
        $queryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        RuleInterface $rule,
        ProductConditionInterface $condition,
        CursorInterface $cursor
    ) {
        $rule->getConditions()->willReturn([$condition]);
        $rule->getCode()->willReturn('therule');
        $condition->getField()->willReturn('field');
        $condition->getOperator()->willReturn('operator');
        $condition->getValue()->willReturn('value');
        $condition->getLocale()->willReturn('fr_FR');
        $condition->getScope()->willReturn('ecommerce');

        $queryBuilderFactory->create()->shouldBeCalled()->willReturn($pqb);
        $pqb->addFilter('field', 'operator', 'value', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);

        $eventDispatcher->dispatch(RuleEvents::PRE_SELECT, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_SELECT, Argument::any())->shouldBeCalled();

        $this->select($rule)->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSet');
    }

    function it_selects_subject_of_a_rule_that_has_conditions(
        $eventDispatcher,
        $queryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        RuleInterface $rule,
        ProductConditionInterface $condition,
        CursorInterface $cursor
    ) {
        $rule->getConditions()->willReturn([$condition]);
        $rule->getCode()->willReturn('therule');
        $condition->getField()->willReturn('field');
        $condition->getOperator()->willReturn('operator');
        $condition->getValue()->willReturn('value');
        $condition->getLocale()->willReturn('fr_FR');
        $condition->getScope()->willReturn('ecommerce');

        $queryBuilderFactory->create()->shouldBeCalled()->willReturn($pqb);
        $eventDispatcher->dispatch(RuleEvents::PRE_SELECT, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_SELECT, Argument::any())->shouldBeCalled();
        $pqb->addFilter('field', 'operator', 'value', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);

        $this->select($rule)->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSet');
    }
}
