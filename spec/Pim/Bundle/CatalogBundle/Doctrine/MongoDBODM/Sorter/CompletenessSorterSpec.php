<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class CompletenessSorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldSorterInterface');
    }

    function it_supports_completeness_field()
    {
        $this->supportsField('completeness')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_adds_a_order_by_completeness_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->sort('normalizedData.completenesses.mobile-en_US', 'desc')->shouldBeCalled();
        $queryBuilder->sort('_id')->shouldBeCalled();

        $this->addFieldSorter('completenesses', 'desc', ['locale' => 'en_US', 'scope' => 'mobile']);
    }
}
