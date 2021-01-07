<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleSelector;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSet;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface;
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
            RuleSubjectSet::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRuleSelector::class);
    }

    function it_should_be_a_selector()
    {
        $this->shouldHaveType(SelectorInterface::class);
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

        $queryBuilderFactory->create(['with_document_type_facet' => true])->shouldBeCalled()->willReturn($pqb);
        $pqb->addFilter('field', 'operator', 'value', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);

        $eventDispatcher->dispatch(Argument::any(), RuleEvents::PRE_SELECT)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), RuleEvents::POST_SELECT)->shouldBeCalled();

        $this->select($rule)->shouldHaveType(RuleSubjectSet::class);
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

        $queryBuilderFactory->create(['with_document_type_facet' => true])->shouldBeCalled()->willReturn($pqb);
        $eventDispatcher->dispatch(Argument::any(), RuleEvents::PRE_SELECT)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), RuleEvents::POST_SELECT)->shouldBeCalled();
        $pqb->addFilter('field', 'operator', 'value', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);

        $this->select($rule)->shouldHaveType(RuleSubjectSet::class);
    }
}
