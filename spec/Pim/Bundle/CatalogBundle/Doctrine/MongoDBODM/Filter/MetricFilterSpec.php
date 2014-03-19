<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class MetricFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith($queryBuilder, 'en_US', 'mobile');
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FilterInterface');
    }

    function it_adds_a_equals_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->equals(22.5)->willReturn($queryBuilder);

        $this->add($metric, '=', '22.5');
    }

    function it_adds_a_greater_than_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->gt(22.5)->willReturn($queryBuilder);

        $this->add($metric, '>', '22.5');
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->gte(22.5)->willReturn($queryBuilder);

        $this->add($metric, '>=', '22.5');
    }

    function it_adds_a_lesser_than_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->lt(22.5)->willReturn($queryBuilder);

        $this->add($metric, '<', '22.5');
    }

    function it_adds_a_lesser_than_or_equals_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->lte(22.5)->willReturn($queryBuilder);

        $this->add($metric, '<=', '22.5');
    }
}
