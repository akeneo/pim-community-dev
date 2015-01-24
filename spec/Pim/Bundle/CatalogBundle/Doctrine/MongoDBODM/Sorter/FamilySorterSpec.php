<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class FamilySorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface');
    }

    function it_supports_family_field()
    {
        $this->supportsField('family')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_adds_a_order_by_on_family_label_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->sort('normalizedData.family.label.en_US', 'desc')->willReturn($queryBuilder);
        $queryBuilder->sort('normalizedData.family.code', 'desc')->willReturn($queryBuilder);
        $queryBuilder->sort('_id')->shouldBeCalled();

        $this->addFieldSorter('family', 'desc', 'en_US');
    }

    function it_throws_an_exception_when_the_locale_is_not_provided()
    {
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringAddFieldSorter('family', 'desc');
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringAddFieldSorter('family', 'desc', null, 'ecommerce');
    }
}
