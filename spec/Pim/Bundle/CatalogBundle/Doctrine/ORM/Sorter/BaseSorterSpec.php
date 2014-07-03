<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

class BaseSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($queryBuilder, $context);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\AttributeSorterInterface');
    }

    function it_adds_a_sorter_in_the_query(QueryBuilder $queryBuilder, AbstractAttribute $sku)
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

        $this->addAttributeSorter($sku, 'DESC');
    }

}
