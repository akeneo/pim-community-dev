<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class BaseSorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Sorter\AttributeSorterInterface');
    }

    function it_is_a_field_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface');
    }

    function it_supports_fields()
    {
        $this->supportsField(Argument::any())->shouldReturn(true);
    }

    function it_supports_attributes(AttributeInterface $attribute)
    {
        $this->supportsAttribute($attribute)->shouldReturn(true);
    }

    function it_adds_a_order_by_on_an_attribute_value_in_the_query(Builder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $queryBuilder->sort('normalizedData.sku', 'desc')->willReturn($queryBuilder);
        $queryBuilder->sort('_id')->shouldBeCalled();

        $this->addAttributeSorter($sku, 'desc');
    }

    function it_adds_a_order_by_on_field_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->sort('normalizedData.my_field', 'desc')->willReturn($queryBuilder);
        $queryBuilder->sort('_id')->shouldBeCalled();

        $this->addFieldSorter('my_field', 'desc');
    }
}
