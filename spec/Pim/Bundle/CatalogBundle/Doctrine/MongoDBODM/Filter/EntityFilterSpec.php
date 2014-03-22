<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class EntityFilterSpec extends ObjectBehavior
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

    function it_adds_a_in_filter_on_an_attribute_value_in_the_query(Builder $queryBuilder, AbstractAttribute $color)
    {
        $color->getCode()->willReturn('color');
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(false);
        $queryBuilder->field('normalizedData.color-en_US.id')->willReturn($queryBuilder);
        $queryBuilder->in([1, 2])->willReturn($queryBuilder);

        $this->addAttributeFilter($color, 'IN', [1, 2]);
    }

    function it_adds_a_in_filter_on_a_field_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('family')->willReturn($queryBuilder);
        $queryBuilder->in([1, 2])->willReturn($queryBuilder);

        $this->addFieldFilter('family', 'IN', [1, 2]);
    }
}
