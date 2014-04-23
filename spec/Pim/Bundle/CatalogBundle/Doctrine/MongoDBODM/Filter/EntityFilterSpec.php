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
class EntityFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($qb, $context);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\AttributeFilterInterface');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface');
    }

    function it_adds_a_in_filter_on_an_attribute_value_in_the_query($qb, AbstractAttribute $color)
    {
        $color->getCode()->willReturn('color');
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(false);
        $qb->field('normalizedData.color-en_US.id')->willReturn($qb);
        $qb->in([1, 2])->willReturn($qb);

        $this->addAttributeFilter($color, 'IN', [1, 2]);
    }

    function it_adds_a_in_filter_on_a_field_in_the_query(Builder $qb)
    {
        $qb->field('family')->willReturn($qb);
        $qb->in([1, 2])->willReturn($qb);

        $this->addFieldFilter('family', 'IN', [1, 2]);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb)
    {
        $qb->field('family')->willReturn($qb);
        $qb->exists(false)->willReturn($qb);

        $this->addFieldFilter('family', 'EMPTY', null);
    }
}
