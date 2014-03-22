<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class FamilySorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith($queryBuilder, 'en_US', 'mobile');
    }

    function it_is_a_field_sorter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface');
    }

    function it_adds_a_order_by_on_family_label_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->sort('normalizedData.family.label.en_US', 'desc')->willReturn($queryBuilder);

        $this->addFieldSorter('family', 'desc');
    }
}
