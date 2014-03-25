<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class CompletenessFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith($queryBuilder, 'en_US', 'mobile');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface');
    }

    function it_adds_a_equals_filter_on_completeness_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->willReturn($queryBuilder);
        $queryBuilder->equals('100')->willReturn($queryBuilder);

        $this->addFieldFilter('completenesses', '=', '100');
    }

    function it_adds_a_less_than_filter_on_completeness_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->willReturn($queryBuilder);
        $queryBuilder->lt('100')->willReturn($queryBuilder);

        $this->addFieldFilter('completenesses', '<', '100');
    }
}
