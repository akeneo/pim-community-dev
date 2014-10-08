<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class PriceFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
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

    function it_adds_a_equals_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $price)
    {
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->equals(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '=', '22.5 EUR');
    }

    function it_adds_a_greater_than_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $price)
    {
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->gt(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '>', '22.5 EUR');
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $price)
    {
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->gte(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '>=', '22.5 EUR');
    }

    function it_adds_a_less_than_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $price)
    {
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->lt(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '<', '22.5 EUR');
    }

    function it_adds_a_less_than_or_equals_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $price)
    {
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->lte(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '<=', '22.5 EUR');
    }
}
