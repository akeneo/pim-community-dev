<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use PhpSpec\ObjectBehavior;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class GroupsFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith(['groups'], ['IN', 'NOT IN']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
    }

    function it_adds_a_in_filter_on_the_groups_field_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('groupIds')->willReturn($queryBuilder);
        $queryBuilder->in([1, 2, 'empty'])->willReturn($queryBuilder);

        $this->addFieldFilter('groups', 'IN', [1, 2, 'empty']);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('groups', 'filter', 'groups'))
            ->during('addFieldFilter', ['groups', 'IN', 'not an array']);
    }

    function it_throws_an_exception_if_content_of_array_is_not_integer_or_empty()
    {
        $this->shouldThrow(InvalidArgumentException::numericExpected('groups', 'filter', 'groups'))
            ->during('addFieldFilter', ['groups', 'IN', [1, 2, 'WRONG']]);
    }
}
