<?php

namespace spec\PimEnterprise\Component\CatalogRule\Engine;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductConditionInterface;
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
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Engine\ProductRuleSelector');
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
