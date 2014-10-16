<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class EntitySorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\EntitySorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeSorterInterface');
    }

    function it_supports_select_attributes(AbstractAttribute $entity)
    {
        $entity->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttribute($entity)->shouldReturn(true);

        $entity->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($entity)->shouldReturn(true);

        $entity->getAttributeType()->willReturn(Argument::any());
        $this->supportsAttribute($entity)->shouldReturn(false);
    }

    function it_adds_a_sorter_to_the_query($qb, AbstractAttribute $entity, Expr $expr)
    {
        $entity->getId()->willReturn('42');
        $entity->getCode()->willReturn('entity_code');
        $entity->isLocalizable()->willReturn(false);
        $entity->isScopable()->willReturn(false);
        $entity->getBackendType()->willReturn('entity');

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
        $qb->addOrderBy('sorterOentity_code.code', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('sorterOVentity_code.value', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addAttributeSorter($entity, 'DESC');
    }
}
