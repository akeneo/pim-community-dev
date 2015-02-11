<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($attrValidatorHelper, ['pim_catalog_boolean'], ['enabled'], ['=']);
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BooleanFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
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
        $this->shouldThrow(InvalidArgumentException::booleanExpected('enabled', 'filter', 'boolean', gettype('fuu')))
            ->during('addFieldFilter', ['enabled', '=', 'fuu']);
    }

    function it_adds_an_equal_filter_on_a_field_in_the_query(
        $qb,
        Expr $expr
    ) {
        $qb->andWhere("p.enabled = true")->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->eq('p.enabled', true)->willReturn("p.enabled = true");
        $expr->literal(true)->willReturn(true);

        $this->addFieldFilter('enabled', '=', true);
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query(
        $qb,
        $attrValidatorHelper,
        AttributeInterface $attribute,
        Expr $expr
    ) {
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $qb->expr()->willReturn($expr);
        $qb->getRootAlias()->willReturn('p');
        $expr->eq(Argument::any(), true)->willReturn('filtercode.backend_type = true');
        $expr->literal(true)->willReturn(true);

        $qb->innerJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, '=', true);
    }
}
