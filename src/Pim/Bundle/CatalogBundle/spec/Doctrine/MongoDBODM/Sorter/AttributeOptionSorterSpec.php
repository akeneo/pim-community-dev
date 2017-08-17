<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;

class AttributeOptionSorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement(AttributeSorterInterface::class);
    }

    function it_supports_only_simple_select_attribute(
        AttributeInterface $simpleSelectAttribute,
        AttributeInterface $booleanAttribute
    ) {
        $simpleSelectAttribute->getType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($simpleSelectAttribute)->shouldReturn(true);

        $booleanAttribute->getType()->willReturn('pim_catalog_boolean');
        $this->supportsAttribute($booleanAttribute)->shouldReturn(false);
    }

    function it_adds_a_order_by_on_attribute_option_label_in_the_query(
        AttributeInterface $simpleSelectAttribute,
        Builder $queryBuilder
    ) {
        $simpleSelectAttribute->isLocalizable()->willReturn(false);
        $simpleSelectAttribute->isScopable()->willReturn(false);
        $simpleSelectAttribute->getCode()->willReturn('color');
        $queryBuilder->sort('normalizedData.color.optionValues.en_US.value', 'desc')->shouldBeCalled();
        $queryBuilder->sort('normalizedData.color.code', 'desc')->shouldBeCalled();

        $this->addAttributeSorter($simpleSelectAttribute, 'desc', 'en_US', 'print')->shouldReturn($this);
    }
}
