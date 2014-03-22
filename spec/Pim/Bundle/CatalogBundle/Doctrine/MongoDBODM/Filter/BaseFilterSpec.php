<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class BaseFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith($queryBuilder, 'en_US', 'mobile');
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\AttributeFilterInterface');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface');
    }

    function it_adds_a_like_filter_on_an_attribute_value_in_the_query(Builder $queryBuilder, AbstractAttribute $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $queryBuilder->field('normalizedData.sku')->willReturn($queryBuilder);
        $queryBuilder->equals('my-sku')->willReturn($queryBuilder);

        $this->addAttributeFilter($sku, 'LIKE', 'my-sku');
    }

    function it_adds_a_like_filter_on_a_field_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('normalizedData.field')->willReturn($queryBuilder);
        $queryBuilder->equals('test')->willReturn($queryBuilder);

        $this->addFieldFilter('field', 'LIKE', 'test');
    }

}
