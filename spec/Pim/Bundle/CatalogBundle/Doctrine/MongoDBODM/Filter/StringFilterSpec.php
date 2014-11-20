<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class StringFilterSpec extends ObjectBehavior
{
    function let(Builder $qb)
    {
        $this->beConstructedWith(['pim_catalog_identifier'], ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN']);
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\StringFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN']);
        $this->supportsOperator('ENDS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_starts_with_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^My Sku/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'STARTS WITH', 'My Sku');
    }

    function it_adds_a_ends_with_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku$/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'ENDS WITH', 'My Sku');
    }

    function it_adds_a_contains_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'CONTAINS', 'My Sku');
    }

    function it_adds_a_does_not_contain_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^((?!My Sku).)*$/i'))->willReturn($qb);

        $this->addAttributeFilter($sku, 'DOES NOT CONTAIN', 'My Sku');
    }

    function it_adds_a_starts_with_field_filter_in_the_query($qb)
    {
        $qb->field('normalizedData.field')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^My Sku/i'))->willReturn($qb);

        $this->addFieldFilter('field', 'STARTS WITH', 'My Sku');
    }

    function it_adds_a_ends_with_field_filter_in_the_query($qb)
    {
        $qb->field('normalizedData.field')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku$/i'))->willReturn($qb);

        $this->addFieldFilter('field', 'ENDS WITH', 'My Sku');
    }

    function it_adds_a_contains_field_filter_in_the_query($qb)
    {
        $qb->field('normalizedData.field')->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku/i'))->willReturn($qb);

        $this->addFieldFilter('field', 'CONTAINS', 'My Sku');
    }

    function it_adds_a_does_not_contain_field_filter_in_the_query($qb)
    {
        $qb->field('normalizedData.field')->willReturn($qb);
        $qb->equals(new \MongoRegex('/^((?!My Sku).)*$/i'))->willReturn($qb);

        $this->addFieldFilter('field', 'DOES NOT CONTAIN', 'My Sku');
    }

    function it_throws_an_exception_if_value_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');
        $this->shouldThrow(InvalidArgumentException::stringExpected('attributeCode', 'filter', 'string'))
            ->during('addAttributeFilter', [$attribute, '=', 123]);
     }
}
