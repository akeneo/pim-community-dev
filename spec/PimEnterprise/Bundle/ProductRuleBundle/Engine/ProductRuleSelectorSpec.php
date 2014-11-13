<?php

namespace spec\PimEnterprise\Bundle\ProductRuleBundle\Engine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductRuleSelectorSpec extends ObjectBehavior
{
    function let(
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

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductRuleBundle\Engine\ProductRuleSelector');
    }

    function it_should_be_a_selector()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface');
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

    function it_selects_subjects_of_a_rule(
        $eventDispatcher,
        $productQueryFactory,
        ProductQueryBuilderInterface $pqb,
        LoadedRuleInterface $rule
    ) {
        $pqb->execute()->willReturn([]);
        $rule->getConditions()->willReturn([]);
        $rule->getCode()->willReturn('therule');

        $productQueryFactory->create()->shouldBeCalled()->willReturn($pqb);
        $eventDispatcher->dispatch(RuleEvents::PRE_SELECT, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_SELECT, Argument::any())->shouldBeCalled();

        $this->select($rule)->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSet');
    }

    function it_selects_subject_of_a_rule_that_has_conditions(
        $eventDispatcher,
        $productQueryFactory,
        ProductQueryBuilderInterface $pqb,
        LoadedRuleInterface $rule
    ) {
        $pqb->execute()->willReturn([]);
        $rule->getConditions()->willReturn([$this->createConditionArray()]);
        $rule->getCode()->willReturn('therule');

        $productQueryFactory->create()->shouldBeCalled()->willReturn($pqb);
        $eventDispatcher->dispatch(RuleEvents::PRE_SELECT, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_SELECT, Argument::any())->shouldBeCalled();
        $pqb->addFilter('field', 'operator', 'value')->shouldBeCalled();

        $this->select($rule)->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSet');
    }

    function it_selects_subject_of_a_rule_that_has_conditions_with_invalid_options(
        $eventDispatcher,
        $productQueryFactory,
        ProductQueryBuilderInterface $pqb,
        LoadedRuleInterface $rule
    ) {
        $pqb->execute()->willReturn([]);
        $rule->getConditions()->willReturn([$this->createConditionArray() + ['invalid_option' => 'foo']]);
        $rule->getCode()->willReturn('therule');

        $productQueryFactory->create()->shouldBeCalled()->willReturn($pqb);
        $eventDispatcher->dispatch(RuleEvents::PRE_SELECT, Argument::any())->shouldBeCalled();

        $this->shouldThrow('\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')
            ->during('select', [$rule]);
    }

    private function createConditionArray()
    {
        return [
            'field' => 'field',
            'operator' => 'operator',
            'value' => 'value',
        ];
    }
}
