<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;

class EntitySorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\EntitySorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface');
    }

    function it_supports_select_attributes(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getType()->willReturn(Argument::any());
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    function it_adds_a_sorter_to_the_query($qb, AttributeInterface $attribute, Expr $expr)
    {
        $attribute->getId()->willReturn('42');
        $attribute->getCode()->willReturn('entity_code');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('entity');

        $qb->getRootAlias()->willReturn('r');
        $qb->expr()->willReturn($expr);

        $qb
            ->leftJoin('r.values', 'sorterVentity_code', 'WITH', 'sorterVentity_code.attribute = 42')
            ->shouldBeCalled()
        ;
        $qb
            ->leftJoin(
                'sorterVentity_code.entity',
                'sorterOentity_code',
                'WITH',
                'sorterOentity_code.attribute = 42'
            )
            ->shouldBeCalled()
        ;
        $expr->literal('en_US')->shouldBeCalled()->willReturn('en_US');
        $qb
            ->leftJoin(
                'sorterOentity_code.optionValues',
                'sorterOVentity_code',
                'WITH',
                'sorterOVentity_code.locale = en_US'
            )
            ->shouldBeCalled()
        ;

        $qb->addSelect('sorterOVentity_code.value AS HIDDEN')->shouldBeCalled();
        $qb->addOrderBy('sorterOVentity_code.value', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('sorterOentity_code.code', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addAttributeSorter($attribute, 'DESC', 'en_US');
    }

    function it_throws_an_exception_when_the_locale_is_not_provided($qb, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('my_code');
        $attribute->getBackendType()->willReturn('options');
        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringAddAttributeSorter($attribute, 'desc', null, 'ecommerce');
    }
}
