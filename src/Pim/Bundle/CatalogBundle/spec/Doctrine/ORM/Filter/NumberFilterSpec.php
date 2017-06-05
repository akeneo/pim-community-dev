<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class NumberFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_number'],
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_binary_filter_in_the_query($attrValidatorHelper, QueryBuilder $queryBuilder, AttributeInterface $number)
    {
        $attrValidatorHelper->validateLocale($number, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($number, Argument::any())->shouldBeCalled();

        $number->getId()->willReturn(42);
        $number->getCode()->willReturn('number');
        $number->getBackendType()->willReturn('text');
        $number->isLocalizable()->willReturn(false);
        $number->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($number, '=', 12);
    }

    function it_adds_empty_filter_in_the_query($attrValidatorHelper, QueryBuilder $queryBuilder, AttributeInterface $number)
    {
        $attrValidatorHelper->validateLocale($number, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($number, Argument::any())->shouldBeCalled();

        $number->getId()->willReturn(42);
        $number->getCode()->willReturn('number');
        $number->getBackendType()->willReturn('text');
        $number->isLocalizable()->willReturn(false);
        $number->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->leftJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $queryBuilder->andWhere(Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($number, 'EMPTY', 12);
    }

    function it_adds_not_empty_filter_in_the_query(
        $attrValidatorHelper,
        QueryBuilder $queryBuilder,
        AttributeInterface $number,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($number, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($number, Argument::any())->shouldBeCalled();

        $number->getId()->willReturn(42);
        $number->getCode()->willReturn('number');
        $number->getBackendType()->willReturn('text');
        $number->isLocalizable()->willReturn(false);
        $number->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn($expr);
        $queryBuilder->getRootAlias()->willReturn('p');

        $expr->isNotNull(Argument::any())->shouldBeCalled()->willReturn('filternumber IS NOT NULL');

        $queryBuilder->leftJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $queryBuilder->andWhere('filternumber IS NOT NULL')->shouldBeCalled();

        $this->addAttributeFilter($number, 'NOT EMPTY', 12);
    }

    function it_adds_not_equal_filter_in_the_query(
        $attrValidatorHelper,
        QueryBuilder $queryBuilder,
        AttributeInterface $number,
        Expr $expr,
        Expr\Comparison $comp,
        Expr\Literal $literal
    ) {
        $attrValidatorHelper->validateLocale($number, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($number, Argument::any())->shouldBeCalled();

        $number->getId()->willReturn(42);
        $number->getCode()->willReturn('number');
        $number->getBackendType()->willReturn('text');
        $number->isLocalizable()->willReturn(false);
        $number->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn($expr);
        $queryBuilder->getRootAlias()->willReturn('p');
        $expr->literal(12)->willReturn($literal);
        $expr->neq(Argument::any(), $literal)->shouldBeCalled()->willReturn($comp);
        $literal->__toString()->willReturn('12');
        $comp->__toString()->willReturn('filtercode.backend_type <> 12');

        $queryBuilder->innerJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::containingString('.attribute = 42 AND filtercode.backend_type <> 12')
        )->shouldBeCalled();

        $this->addAttributeFilter($number, '!=', 12);
    }

    function it_throws_an_exception_if_value_is_not_a_numeric(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('number_code');
        $this->shouldThrow(InvalidPropertyTypeException::numericExpected(
            'number_code', 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\NumberFilter',
            'WRONG'
        ))->during('addAttributeFilter', [$attribute, '=', 'WRONG']);
    }
}
