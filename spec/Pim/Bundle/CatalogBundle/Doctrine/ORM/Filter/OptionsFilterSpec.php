<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

class OptionsFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['pim_catalog_multiselect'], ['IN', 'EMPTY']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_multi_select_attribute(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_filter_to_the_query($qb, AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->innerJoin(
            'r.values',
            'filteroptions_code',
            'WITH',
            'filteroptions_code.attribute = 42'
        )->shouldBeCalled()->willReturn($qb);
        $qb->innerJoin(
            'filteroptions_code.options',
            'filterOoptions_code',
            'WITH',
            'filterOoptions_code.id IN(\'22\', \'42\')'
        )->shouldBeCalled()->willReturn($qb);

        $this->addAttributeFilter($attribute, 'IN', ['22', '42']);
    }

    function it_adds_an_empty_filter_to_the_query($qb, AttributeInterface $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('options');
        $attribute->getCode()->willReturn('options_code');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn(new Expr());

        $qb->leftJoin(
            'r.values',
            'filteroptions_code',
            'WITH',
            'filteroptions_code.attribute = 42'
        )->shouldBeCalled()->willReturn($qb);
        $qb->leftJoin('filteroptions_code.options', 'filterOoptions_code')->shouldBeCalled()->willReturn($qb);
        $qb
            ->andWhere(
                'filterOoptions_code.id IS NULL'
            )
            ->shouldBeCalled()
        ;

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidArgumentException::arrayExpected('option_code', 'filter', 'options'))
            ->during('addAttributeFilter', [$attribute, 'IN', 'WRONG']);
    }

    function it_throws_an_exception_if_the_content_of_value_are_not_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('option_code');
        $this->shouldThrow(InvalidArgumentException::numericExpected('option_code', 'filter', 'options'))
            ->during('addAttributeFilter', [$attribute, 'IN', [123, 'not numeric']]);
    }
}
