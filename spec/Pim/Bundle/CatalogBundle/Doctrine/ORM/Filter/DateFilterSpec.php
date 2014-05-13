<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

class DateFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($qb, $context);

        $qb->getRootAliases()->willReturn(array('p'));
    }

    function it_is_a_date_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter');
    }

    function it_is_a_base_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BaseFilter');
    }

    function it_adds_a_less_than_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date < '2014-03-15'")->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->lt('p.release_date', '2014-03-15')->willReturn("p.release_date < '2014-03-15'");
        $expr->literal('2014-03-15')->willReturn('2014-03-15');

        $this->addFieldFilter('release_date', '<', '2014-03-15');
    }

    function it_adds_a_greater_than_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date > '2014-03-15 23:59:59'")->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-15 23:59:59')->willReturn("p.release_date > '2014-03-15 23:59:59'");
        $expr->literal('2014-03-15 23:59:59')->willReturn('2014-03-15 23:59:59');

        $this->addFieldFilter('release_date', '>', '2014-03-15');
    }

    function it_adds_a_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date > '2014-03-15' AND p.release_date < '2014-03-20 23:59:59'")->willReturn($qb);
        $expr->andX("p.release_date > '2014-03-15'", "p.release_date < '2014-03-20 23:59:59'")->willReturn("p.release_date > '2014-03-15' AND p.release_date < '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-15')->willReturn("p.release_date > '2014-03-15'");
        $expr->lt('p.release_date', '2014-03-20 23:59:59')->willReturn("p.release_date < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', 'BETWEEN', array('2014-03-15', '2014-03-20'));
    }

    function it_adds_an_equal_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date > '2014-03-20' AND p.release_date < '2014-03-20 23:59:59'")->willReturn($qb);
        $expr->andX("p.release_date > '2014-03-20'", "p.release_date < '2014-03-20 23:59:59'")->willReturn("p.release_date > '2014-03-20' AND p.release_date < '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->gt('p.release_date', '2014-03-20')->willReturn("p.release_date > '2014-03-20'");
        $expr->lt('p.release_date', '2014-03-20 23:59:59')->willReturn("p.release_date < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-20')->willReturn('2014-03-20');
        $expr->literal('2014-03-20 23:59:59')->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', '=', '2014-03-20');
    }

    function it_adds_a_not_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.release_date < '2014-03-15' OR p.release_date > '2014-03-20 23:59:59'")->willReturn($qb);
        $expr->orX("p.release_date < '2014-03-15'", "p.release_date > '2014-03-20 23:59:59'")->willReturn("p.release_date < '2014-03-15' OR p.release_date > '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->lt('p.release_date', '2014-03-15')->willReturn("p.release_date < '2014-03-15'");
        $expr->gt('p.release_date', '2014-03-20 23:59:59')->willReturn("p.release_date > '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('release_date', 'NOT BETWEEN', array('from' => '2014-03-15', 'to' => '2014-03-20'));
    }
}
