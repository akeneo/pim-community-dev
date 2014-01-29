<?php

namespace spec\Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

class BaseSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith($queryBuilder, 'en_US', 'mobile');
    }

    function it_is_a_sorter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\FlexibleEntityBundle\Doctrine\SorterInterface');
    }

    function it_adds_a_sorter_in_the_query(QueryBuilder $queryBuilder, Attribute $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->getBackendStorage()->willReturn('values');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->getDQLPart('join')->willReturn([]);
        $queryBuilder->resetDQLPart('join')->shouldBeCalled();

        $condition = "sorterVsku1.attribute = 42";
        $queryBuilder->leftJoin('p.values', 'sorterVsku1', 'WITH', $condition)->shouldBeCalled();
        $queryBuilder->addOrderBy('sorterVsku1.varchar', 'DESC')->shouldBeCalled();

        $this->add($sku, 'DESC');
    }

}
