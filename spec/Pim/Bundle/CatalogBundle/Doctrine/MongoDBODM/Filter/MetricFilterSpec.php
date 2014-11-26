<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class MetricFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith(['pim_catalog_metric'], ['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_metric_attribute(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_equals_filter_in_the_query(Builder $queryBuilder, AttributeInterface $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->equals(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($metric, '=', '22.5', 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_filter_in_the_query(Builder $queryBuilder, AttributeInterface $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->gt(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($metric, '>', '22.5', 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query(Builder $queryBuilder, AttributeInterface $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->gte(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($metric, '>=', '22.5', 'en_US', 'mobile');
    }

    function it_adds_a_less_than_filter_in_the_query(Builder $queryBuilder, AttributeInterface $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->lt(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($metric, '<', '22.5', 'en_US', 'mobile');
    }

    function it_adds_a_less_than_or_equals_filter_in_the_query(Builder $queryBuilder, AttributeInterface $metric)
    {
        $metric->getCode()->willReturn('weight');
        $metric->isLocalizable()->willReturn(true);
        $metric->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.weight-en_US-mobile.baseData')->willReturn($queryBuilder);
        $queryBuilder->lte(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($metric, '<=', '22.5', 'en_US', 'mobile');
    }

    function it_throws_an_exception_if_value_is_not_a_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('metric_code');
        $this->shouldThrow(InvalidArgumentException::numericExpected('metric_code', 'filter', 'metric'))
            ->during('addAttributeFilter', [$attribute, '=', 'WRONG']);
    }
}
