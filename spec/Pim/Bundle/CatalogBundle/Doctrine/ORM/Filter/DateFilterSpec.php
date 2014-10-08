<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class DateFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(array('p'));
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

    function it_supports_date_attributes(AbstractAttribute $dateAtt, AbstractAttribute $otherAtt)
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

        $this->addFieldFilter('release_date', 'BETWEEN', array('2014-03-15', '2014-03-20'));
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

        $this->addFieldFilter('release_date', 'NOT BETWEEN', array('2014-03-15', '2014-03-20'));
    }

    function it_adds_an_empty_operator_filter_on_an_attribute_to_the_query(
        AbstractAttribute $attribute,
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
        AbstractAttribute $attribute,
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
