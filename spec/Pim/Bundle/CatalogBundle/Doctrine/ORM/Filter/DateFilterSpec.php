<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class DateFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_date'],
            ['created', 'updated'],
            ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY']
        );
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_a_date_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY']);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_date_fields()
    {
        $this->supportsField('created')->shouldReturn(true);
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_supports_date_attributes(AttributeInterface $dateAtt, AttributeInterface $otherAtt)
    {
        $dateAtt->getAttributeType()->willReturn('pim_catalog_date');
        $this->supportsAttribute($dateAtt)->shouldReturn(true);
        $otherAtt->getAttributeType()->willReturn('pim_catalog_other');
        $this->supportsAttribute($otherAtt)->shouldReturn(false);
    }

    function it_adds_a_less_than_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date < '2014-03-15'")->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->lt('p.release_date', '2014-03-15')->willReturn("p.release_date < '2014-03-15'")->shouldBeCalledTimes(2);
        $expr->literal('2014-03-15')->willReturn('2014-03-15')->shouldBeCalledTimes(2);

        $this->addFieldFilter('release_date', '<', '2014-03-15');
        $this->addFieldFilter('release_date', '<', new \Datetime('2014-03-15'));
    }

    function it_adds_a_empty_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNull('p.release_date')->willReturn('p.release_date IS NULL');
        $qb->andWhere('p.release_date IS NULL')->shouldBeCalled();

        $this->addFieldFilter('release_date', 'EMPTY', '');
    }

    function it_adds_a_greater_than_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date > '2014-03-15 23:59:59'")->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-15 23:59:59')
            ->shouldBeCalled()
            ->willReturn("p.release_date > '2014-03-15 23:59:59'")
            ->shouldBeCalledTimes(2);
        $expr->literal('2014-03-15 23:59:59')->willReturn('2014-03-15 23:59:59')->shouldBeCalledTimes(2);

        $this->addFieldFilter('release_date', '>', '2014-03-15');
        $this->addFieldFilter('release_date', '>', new \Datetime('2014-03-15'));
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_a_datetime()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('release_date', 'array with 2 elements, string or \Datetime', 'filter', 'date', print_r(123, true))
        )->during('addFieldFilter', ['release_date', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('release_date', 'a string with the format yyyy-mm-dd', 'filter', 'date', 'not a valid date format')
        )->during('addFieldFilter', ['release_date', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'release_date',
                'array with 2 elements, string or \Datetime',
                'filter',
                'date',
                123
            )
        )->during('addFieldFilter', ['release_date', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'release_date',
                'array with 2 elements, string or \Datetime',
                'filter',
                'date',
                print_r([123, 123, 'three'], true)
            )
        )->during('addFieldFilter', ['release_date', '>', [123, 123, 'three']]);
    }

    function it_adds_a_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb
            ->andWhere("p.release_date > '2014-03-15' AND p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn($qb);
        $expr
            ->andX("p.release_date > '2014-03-15'", "p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date > '2014-03-15' AND p.release_date < '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-15')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date > '2014-03-15'");
        $expr->lt('p.release_date', '2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')->willReturn('2014-03-20 23:59:59')->shouldBeCalledTimes(2);

        $this->addFieldFilter('release_date', 'BETWEEN', ['2014-03-15', '2014-03-20']);
        $this->addFieldFilter('release_date', 'BETWEEN', [new \Datetime('2014-03-15'), new \Datetime('2014-03-20')]);
    }

    function it_adds_an_equal_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date > '2014-03-20' AND p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn($qb);
        $expr
            ->andX("p.release_date > '2014-03-20'", "p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date > '2014-03-20' AND p.release_date < '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-20')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date > '2014-03-20'");
        $expr->lt('p.release_date', '2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-20')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20');
        $expr->literal('2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', '=', '2014-03-20');
        $this->addFieldFilter('release_date', '=', new \Datetime('2014-03-20'));
    }

    function it_adds_a_not_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date < '2014-03-15' OR p.release_date > '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn($qb);
        $expr
            ->orX("p.release_date < '2014-03-15'", "p.release_date > '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date < '2014-03-15' OR p.release_date > '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->lt('p.release_date', '2014-03-15')->shouldBeCalledTimes(2)->willReturn("p.release_date < '2014-03-15'");
        $expr->gt('p.release_date', '2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.release_date > '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', 'NOT BETWEEN', ['2014-03-15', '2014-03-20']);
        $this->addFieldFilter('release_date', 'NOT BETWEEN', [new \Datetime('2014-03-15'), new \Datetime('2014-03-20')]);
    }

    function it_adds_an_empty_operator_filter_on_an_attribute_to_the_query(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        QueryBuilder $qb,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $qb->getRootAlias()->willReturn('p');
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $qb->expr()->willReturn($expr);
        $qb->andWhere(null)->willReturn($expr);

        $qb->leftJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_adds_a_greater_than_filter_on_an_attribute_to_the_query(
        AttributeInterface $attribute,
        $attrValidatorHelper,
        QueryBuilder $qb,
        Expr $expr,
        Expr\Comparison $comparison
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $qb->getRootAlias()->willReturn('p');
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('code');
        $expr->literal('en_US')->willReturn('code');
        $expr->literal('mobile')->willReturn('code');

        $expr->gt(Argument::any(), 'code')->willReturn($comparison)->shouldBeCalledTimes(2);
        $comparison->__toString()->willReturn();

        $qb->innerJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalledTimes(2);

        $this->addAttributeFilter($attribute, '>', '2014-03-15');
        $this->addAttributeFilter($attribute, '>', new \DateTime('2014-03-15'));
    }
}
