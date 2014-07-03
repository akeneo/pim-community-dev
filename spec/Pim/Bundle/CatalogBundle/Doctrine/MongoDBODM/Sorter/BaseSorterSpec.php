<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class BaseSorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($queryBuilder, $context);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\AttributeSorterInterface');
    }

    function it_is_a_field_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface');
    }

    function it_adds_a_order_by_on_an_attribute_value_in_the_query(Builder $queryBuilder, AbstractAttribute $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $queryBuilder->sort('normalizedData.sku', 'desc')->willReturn($queryBuilder);

        $this->addAttributeSorter($sku, 'desc');
    }

    function it_adds_a_order_by_on_field_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->sort('normalizedData.my_field', 'desc')->willReturn($queryBuilder);

        $this->addFieldSorter('my_field', 'desc');
    }
}
