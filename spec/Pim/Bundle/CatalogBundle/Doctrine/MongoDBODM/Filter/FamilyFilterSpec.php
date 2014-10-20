<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class FamilyFilterSpec extends ObjectBehavior
{
    function let(Builder $qb)
    {
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_in_filter_on_an_attribute_value_in_the_query($qb, AbstractAttribute $color)
    {
        $color->getCode()->willReturn('color');
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(false);
        $qb->field('normalizedData.color-en_US.id')->willReturn($qb);
        $qb->in([1, 2])->willReturn($qb);

        $this->addAttributeFilter($color, 'IN', [1, 2], ['locale' => 'en_US']);
    }

    function it_adds_a_in_filter_on_a_field_in_the_query($qb)
    {
        $qb->addOr(Argument::type('Doctrine\MongoDB\Query\Expr'))->willReturn($qb);

        $this->addFieldFilter('family', 'IN', [1, 2]);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb)
    {
        $qb->addOr(Argument::type('Doctrine\MongoDB\Query\Expr'))->willReturn($qb);

        $this->addFieldFilter('family', 'IN', ['empty']);
    }

    function it_adds_empty_and_in_filters_on_a_field_in_the_query($qb)
    {
        $qb->addOr(Argument::type('Doctrine\MongoDB\Query\Expr'))->willReturn($qb);

        $this->addFieldFilter('family', 'IN', ['empty', 1, 2]);
    }
}
