<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class BaseSorterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeSorterInterface');
    }

    function it_is_a_field_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldSorterInterface');
    }

    function it_supports_fields()
    {
        $this->supportsField(Argument::any())->shouldReturn(true);
    }

    function it_supports_attributes(AbstractAttribute $attribute)
    {
        $this->supportsAttribute($attribute)->shouldReturn(true);
    }

    function it_adds_a_order_by_on_an_attribute_value_in_the_query(Builder $queryBuilder, AbstractAttribute $sku)
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
