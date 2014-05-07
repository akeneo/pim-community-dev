<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class DateFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($queryBuilder, $context);

        $queryBuilder->field(Argument::any())->willReturn($queryBuilder);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\AttributeFilterInterface');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface');
    }

    function it_adds_a_less_than_filter_on_an_attribute_value_in_the_query($queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->lt(strtotime('2014-03-15'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, '<', '2014-03-15');
    }

    function it_adds_a_greater_than_filter_on_an_attribute_value_in_the_query($queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->gt(strtotime('2014-03-15'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, '>', '2014-03-15');
    }

    function it_adds_a_between_filter_on_an_attribute_value_in_the_query($queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->gt(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-20'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, 'BETWEEN', ['2014-03-15', '2014-03-20']);
    }

    function it_adds_a_not_between_filter_on_an_attribute_value_in_the_query($queryBuilder, AbstractAttribute $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(true);
        $date->isScopable()->willReturn(true);
        $queryBuilder->expr()->willReturn($queryBuilder);
        $queryBuilder->addAnd($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->gt(strtotime('2014-03-20'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, ['from' => '<', 'to' => '>'], ['from' => '2014-03-15', 'to' => '2014-03-20']);
    }

    function it_adds_a_between_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->gt(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-20'))->willReturn($queryBuilder);

        $this->addFieldFilter('created', 'BETWEEN', ['2014-03-15', '2014-03-20']);
    }
}
