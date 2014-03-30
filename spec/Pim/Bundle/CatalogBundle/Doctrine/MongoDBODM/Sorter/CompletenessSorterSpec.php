<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class CompletenessSorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($queryBuilder, $context);
    }

    function it_is_a_field_sorter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface');
    }

    function it_adds_a_order_by_completeness_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->sort('normalizedData.completenesses.mobile-en_US', 'desc')->willReturn($queryBuilder);

        $this->addFieldSorter('completenesses', 'desc');
    }
}
