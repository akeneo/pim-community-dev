<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class FamilyFilterSpec extends ObjectBehavior
{
    function let(Builder $qb)
    {
        $this->beConstructedWith(['family'], ['IN', 'NOT IN']);
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

    function it_adds_a_in_filter_on_a_field_in_the_query($qb)
    {
        $qb->addAnd(Argument::type('Doctrine\MongoDB\Query\Expr'))->willReturn($qb);

        $this->addFieldFilter('family', 'IN', [1, 2]);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb)
    {
        $qb->addAnd(Argument::type('Doctrine\MongoDB\Query\Expr'))->willReturn($qb);

        $this->addFieldFilter('family', 'IN', ['empty']);
    }

    function it_adds_empty_and_in_filters_on_a_field_in_the_query($qb)
    {
        $qb->addAnd(Argument::type('Doctrine\MongoDB\Query\Expr'))->willReturn($qb);

        $this->addFieldFilter('family', 'IN', ['empty', 1, 2]);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('family', 'filter', 'family'))
            ->during('addFieldFilter', ['family', 'IN', 'not an array']);
    }

    function it_throws_an_exception_if_content_of_array_is_not_integer_or_empty()
    {
        $this->shouldThrow(InvalidArgumentException::integerExpected('family', 'filter', 'family'))
            ->during('addFieldFilter', ['family', 'IN', [1, 2, 'WRONG']]);
    }

}
