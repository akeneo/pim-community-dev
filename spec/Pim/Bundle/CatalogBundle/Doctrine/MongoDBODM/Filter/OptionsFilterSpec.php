<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class OptionsFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionsFilter');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_multi_select_attribute(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_an_in_filter_to_the_query($qb, AttributeInterface $attribute, Expr $expr)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->expr()->willReturn($expr);
        $expr->field('normalizedData.options_code.id' )->shouldBeCalled()->willReturn($expr);
        $expr->in([118, 270])->shouldBeCalled()->willReturn($expr);
        $qb->addOr($expr)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['118', '270']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, AttributeInterface $attribute, Expr $expr)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->expr()->willReturn($expr);
        $expr->field('normalizedData.options_code' )->shouldBeCalled()->willReturn($expr);
        $expr->exists(false)->shouldBeCalled()->willReturn($expr);
        $qb->addOr($expr)->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'IN', ['empty']);
    }

    function it_adds_an_empty_filter_and_another_filter_to_the_query($qb, AttributeInterface $attribute, Expr $expr)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $expr->field('normalizedData.options_code' )->shouldBeCalled()->willReturn($expr);
        $expr->field('normalizedData.options_code.id' )->shouldBeCalled()->willReturn($expr);
        $expr->exists(false)->shouldBeCalled()->willReturn($expr);
        $expr->in([1 => 118, 2 => 270])->shouldBeCalled()->willReturn($expr);

        $qb->expr()->willReturn($expr);
        $qb->addOr($expr)->shouldBeCalledTimes(2);

        $this->addAttributeFilter($attribute, 'IN', ['empty', '118', '270']);
    }

    function it_adds_a_not_in_filter_to_the_query($qb, AttributeInterface $attribute, Expr $expr)
    {
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->expr()->willReturn($expr);
        $qb->field('normalizedData.options_code' )->shouldBeCalled()->willReturn($qb);
        $qb->notIn(['118', '270'])->shouldBeCalled()->willReturn($qb);

        $this->addAttributeFilter($attribute, 'NOT IN', ['118', '270']);
    }
}
