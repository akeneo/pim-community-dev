<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class DateFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith($queryBuilder, 'en_US', 'mobile');
    }

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FilterInterface');
    }

    function it_adds_a_lesser_than_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.release_date-en_US-mobile')->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-15'))->willReturn($queryBuilder);

        $this->add($date, '<', '2014-03-15');
    }

    function it_adds_a_greater_than_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.release_date-en_US-mobile')->willReturn($queryBuilder);
        $queryBuilder->gt(strtotime('2014-03-15'))->willReturn($queryBuilder);

        $this->add($date, '>', '2014-03-15');
    }

    function it_adds_a_between_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.release_date-en_US-mobile')->willReturn($queryBuilder);
        $queryBuilder->gt(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-20'))->willReturn($queryBuilder);

        $this->add($date, 'BETWEEN', ['2014-03-15', '2014-03-20']);
    }

    function it_adds_a_not_between_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.release_date-en_US-mobile')->willReturn($queryBuilder);
        $queryBuilder->expr()->willReturn($queryBuilder);
        $queryBuilder->addAnd($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->gt(strtotime('2014-03-20'))->willReturn($queryBuilder);

        $this->add($date, ['from' => '<', 'to' => '>'], ['from' => '2014-03-15', 'to' => '2014-03-20']);
    }
}
