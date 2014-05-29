<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

class EntityFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($qb, $context);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\AttributeFilterInterface');
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface');
    }

    function it_adds_a_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', 'filterfamily')->willReturn($qb);
        $qb->andWhere('filterfamily.id IN (1, 2)')->willReturn($qb);

        $expr->in('filterfamily.id', [1, 2])->willReturn('filterfamily.id IN (1, 2)');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', [1, 2]);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', 'filterfamily')->willReturn($qb);
        $qb->andWhere('filterfamily.id IS NULL')->willReturn($qb);

        $expr->isNull('filterfamily.id')->willReturn('filterfamily.id IS NULL');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', ['empty']);
    }
}
