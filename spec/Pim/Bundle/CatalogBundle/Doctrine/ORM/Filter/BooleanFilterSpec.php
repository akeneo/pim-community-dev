<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
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
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('FAKE')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_a_boolean()
    {
        $this->shouldThrow(InvalidArgumentException::booleanExpected('enabled', 'filter', 'boolean'))
            ->during('addFieldFilter', ['enabled', '=', 'fuu']);
    }

    function it_adds_an_equal_filter_on_a_field_in_the_query(
        $qb,
        Expr $expr
    ) {
        $qb->andWhere("p.enabled = true")->shouldBeCalled()->willReturn($qb);
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $expr->eq('p.enabled', true)->shouldBeCalled()->willReturn("p.enabled = true");
        $expr->literal(true)->shouldBeCalled()->willReturn(true);

        $this->addFieldFilter('enabled', '=', true);
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query(
        $qb,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $qb->getRootAlias()->willReturn('p');
        $expr->eq('filtercode.backend_type', true)->shouldBeCalled()->willReturn("filtercode.backend_type = true");
        $expr->literal(true)->shouldBeCalled()->willReturn(true);

        $qb->innerJoin(
            'p.values',
            'filtercode',
            'WITH',
            'filtercode.attribute = 42 AND filtercode.backend_type = true'
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, '=', true);
    }
}
