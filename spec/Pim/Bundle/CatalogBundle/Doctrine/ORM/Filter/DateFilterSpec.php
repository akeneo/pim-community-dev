<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class DateFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['pim_catalog_date'], ['created', 'updated'], ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY']);
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_a_date_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
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
        $qb->andWhere("p.release_date < '2014-03-15'")->shouldBeCalled()->willReturn($qb);
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $expr->lt('p.release_date', '2014-03-15')->shouldBeCalled()->willReturn("p.release_date < '2014-03-15'");
        $expr->literal('2014-03-15')->shouldBeCalled()->willReturn('2014-03-15');

        $this->addFieldFilter('release_date', '<', '2014-03-15');
    }

    function it_adds_a_empty_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->isNull('p.release_date')->shouldBeCalled()->willReturn('p.release_date IS NULL');
        $qb->andWhere('p.release_date IS NULL')->shouldBeCalled();

        $this->addFieldFilter('release_date', 'EMPTY', '');
    }

    function it_adds_a_greater_than_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date > '2014-03-15 23:59:59'")->shouldBeCalled()->willReturn($qb);
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-15 23:59:59')
            ->shouldBeCalled()
            ->willReturn("p.release_date > '2014-03-15 23:59:59'");
        $expr->literal('2014-03-15 23:59:59')->shouldBeCalled()->willReturn('2014-03-15 23:59:59');

        $this->addFieldFilter('release_date', '>', '2014-03-15');
    }

    function it_throws_an_exception_if_value_is_not_a_string_or_an_array()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('release_date', 'array or string', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format() {
        $this->shouldThrow(
            InvalidArgumentException::expected('release_date', 'a string with the format yyyy-mm-dd', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings()
    {
        $this->shouldThrow(
            InvalidArgumentException::stringExpected('release_date', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::stringExpected('release_date', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', [123, 123, 'three']]);
    }

    function it_adds_a_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb
            ->andWhere("p.release_date > '2014-03-15' AND p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalled()
            ->willReturn($qb);
        $expr
            ->andX("p.release_date > '2014-03-15'", "p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalled()
            ->willReturn("p.release_date > '2014-03-15' AND p.release_date < '2014-03-20 23:59:59'");
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-15')
            ->shouldBeCalled()
            ->willReturn("p.release_date > '2014-03-15'");
        $expr->lt('p.release_date', '2014-03-20 23:59:59')
            ->shouldBeCalled()
            ->willReturn("p.release_date < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')
            ->shouldBeCalled()
            ->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')->shouldBeCalled()->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', 'BETWEEN', ['2014-03-15', '2014-03-20']);
    }

    function it_adds_an_equal_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date > '2014-03-20' AND p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalled()
            ->willReturn($qb);
        $expr
            ->andX("p.release_date > '2014-03-20'", "p.release_date < '2014-03-20 23:59:59'")
            ->shouldBeCalled()
            ->willReturn("p.release_date > '2014-03-20' AND p.release_date < '2014-03-20 23:59:59'");
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-20')
            ->shouldBeCalled()
            ->willReturn("p.release_date > '2014-03-20'");
        $expr->lt('p.release_date', '2014-03-20 23:59:59')
            ->shouldBeCalled()
            ->willReturn("p.release_date < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-20')
            ->shouldBeCalled()
            ->willReturn('2014-03-20');
        $expr->literal('2014-03-20 23:59:59')
            ->shouldBeCalled()
            ->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', '=', '2014-03-20');
    }

    function it_adds_a_not_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date < '2014-03-15' OR p.release_date > '2014-03-20 23:59:59'")
            ->shouldBeCalled()
            ->willReturn($qb);
        $expr
            ->orX("p.release_date < '2014-03-15'", "p.release_date > '2014-03-20 23:59:59'")
            ->shouldBeCalled()
            ->willReturn("p.release_date < '2014-03-15' OR p.release_date > '2014-03-20 23:59:59'");
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $expr->lt('p.release_date', '2014-03-15')->shouldBeCalled()->willReturn("p.release_date < '2014-03-15'");
        $expr->gt('p.release_date', '2014-03-20 23:59:59')
            ->shouldBeCalled()
            ->willReturn("p.release_date > '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')
            ->shouldBeCalled()
            ->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')
            ->shouldBeCalled()
            ->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', 'NOT BETWEEN', ['2014-03-15', '2014-03-20']);
    }

    function it_adds_an_empty_operator_filter_on_an_attribute_to_the_query(
        AttributeInterface $attribute,
        QueryBuilder $qb,
        Expr $expr
    ) {
        $qb->getRootAlias()->willReturn('p');
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $qb->andWhere(null)->shouldBeCalled()->willReturn($expr);

        $qb->leftJoin(
            'p.values',
            'filtercode',
            'WITH',
            'filtercode.attribute = 42'
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_adds_a_greater_than_filter_on_an_attribute_to_the_query(
        AttributeInterface $attribute,
        QueryBuilder $qb,
        Expr $expr,
        Expr\Comparison $comparison
    ) {
        $qb->getRootAlias()->willReturn('p');
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('code');
        $expr->literal('en_US')->willReturn('code');
        $expr->literal('mobile')->willReturn('code');

        $expr->gt('filtercode.backend_type', 'code')->willReturn($comparison)->shouldBeCalled();
        $comparison->__toString()->willReturn();

        $qb->innerJoin(
            'p.values',
            'filtercode',
            'WITH',
            'filtercode.attribute = 42 AND '
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, '>', '2014-03-15');
    }
}
