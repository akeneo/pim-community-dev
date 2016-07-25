<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class StringFilterSpec extends ObjectBehavior
{
    function let(Builder $qb)
    {
        $this->beConstructedWith(
            ['pim_catalog_identifier'],
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\StringFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'STARTS WITH',
            'ENDS WITH',
            'CONTAINS',
            'DOES NOT CONTAIN',
            '=',
            'EMPTY',
            'NOT EMPTY',
            '!='
        ]);
        $this->supportsOperator('ENDS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_starts_with_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/^My Sku/i'))->shouldBeCalled();

        $this->addAttributeFilter($sku, 'STARTS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_ends_with_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku$/i'))->shouldBeCalled();

        $this->addAttributeFilter($sku, 'ENDS WITH', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_contains_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/My Sku/i'))->shouldBeCalled();

        $this->addAttributeFilter($sku, 'CONTAINS', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_does_not_contain_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/^((?!My Sku).)*$/i'))->shouldBeCalled();

        $this->addAttributeFilter($sku, 'DOES NOT CONTAIN', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_an_equals_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->equals('My Sku')->shouldBeCalled();

        $this->addAttributeFilter($sku, '=', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_a_not_equal_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();
        $qb->notEqual('My Sku')->shouldBeCalled();

        $this->addAttributeFilter($sku, '!=', 'My Sku', null, null, ['field' => 'sku']);
    }

    function it_adds_an_empty_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->exists(false)->shouldBeCalled()->shouldBeCalled();

        $this->addAttributeFilter($sku, 'EMPTY', null, null, null, ['field' => 'sku']);
    }

    function it_adds_a_not_empty_attribute_filter_in_the_query($qb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->field('normalizedData.sku')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();

        $this->addAttributeFilter($sku, 'NOT EMPTY', null, null, null, ['field' => 'sku']);
    }


    function it_throws_an_exception_if_value_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');

        $this->shouldThrow(InvalidArgumentException::stringExpected('attributeCode', 'filter', 'string', gettype(123)))
            ->during('addAttributeFilter', [$attribute, '=', 123, null, null, ['field' => 'attributeCode']]);
    }
}
