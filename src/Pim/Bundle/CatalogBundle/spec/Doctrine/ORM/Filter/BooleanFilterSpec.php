<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['pim_catalog_boolean'], ['enabled'], ['=']);
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BooleanFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['enabled']);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('FAKE')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_a_boolean()
    {
        $this->shouldThrow(InvalidArgumentException::booleanExpected('enabled', 'filter', 'boolean', gettype('fuu')))
            ->during('addFieldFilter', ['enabled', '=', 'fuu']);
    }

    function it_adds_an_equal_filter_on_a_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp,
        Expr\Literal $literal
    ) {
        $qb->andWhere('p.enabled = true')->shouldBeCalled()->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->eq('p.enabled', true)->willReturn($comp);
        $expr->literal(true)->willReturn($literal);
        $literal->__toString()->willReturn('true');
        $comp->__toString()->willReturn('p.enabled = true');

        $this->addFieldFilter('enabled', '=', true);
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query(
        $qb,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Comparison $comp,
        Expr\Literal $literal
    ) {
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->expr()->willReturn($expr);
        $qb->getRootAlias()->willReturn('p');
        $expr->eq(Argument::any(), true)->shouldBeCalled()->willReturn($comp);
        $expr->literal(true)->willReturn($literal);
        $literal->__toString()->willReturn('true');
        $comp->__toString()->willReturn('filtercode.backend_type = true');

        $qb->innerJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, '=', true);
    }

    function it_adds_a_not_equal_filter_on_a_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp,
        Expr\Literal $literal
    ) {
        $qb->andWhere('p.enabled <> true')->shouldBeCalled()->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->neq('p.enabled', true)->willReturn($comp);
        $expr->literal(true)->willReturn($literal);
        $comp->__toString()->willReturn('p.enabled <> true');
        $literal->__toString()->willReturn('true');

        $this->addFieldFilter('enabled', '!=', true);
    }

    function it_adds_a_not_equal_filter_on_an_attribute_in_the_query(
        $qb,
        AttributeInterface $attribute,
        Expr $expr,
        Expr\Comparison $comp,
        Expr\Literal $literal
    ) {
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->expr()->willReturn($expr);
        $qb->getRootAlias()->willReturn('p');
        $expr->literal(true)->willReturn($literal);
        $expr->neq(Argument::any(), $literal)->shouldBeCalled()->willReturn($comp);
        $literal->__toString()->willReturn('true');
        $comp->__toString()->willReturn('filtercode.backend_type <> true');

        $qb->innerJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, '!=', true);
    }
}
