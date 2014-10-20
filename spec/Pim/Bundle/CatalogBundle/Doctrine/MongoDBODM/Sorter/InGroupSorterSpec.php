<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class InGroupSorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldSorterInterface');
    }

    function it_supports_in_group_field()
    {
        $this->supportsField('in_group_field')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_adds_a_order_by_in_group_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->sort('normalizedData.in_group_12', 'desc')->willReturn($queryBuilder);
        $queryBuilder->sort('_id')->shouldBeCalled();

        $this->addFieldSorter('in_group_12', 'desc');
    }
}
