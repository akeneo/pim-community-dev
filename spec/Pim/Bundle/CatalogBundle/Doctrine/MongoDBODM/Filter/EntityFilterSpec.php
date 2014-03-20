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

    function it_is_a_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FilterInterface');
    }

    function it_adds_a_in_filter_in_the_query(Builder $queryBuilder, AbstractAttribute $color)
    {
        $color->getCode()->willReturn('color');
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(false);
        $queryBuilder->field('normalizedData.color-en_US.id')->willReturn($queryBuilder);
        $queryBuilder->in([1, 2])->willReturn($queryBuilder);

        $this->add($color, 'IN', [1, 2]);
    }
}
