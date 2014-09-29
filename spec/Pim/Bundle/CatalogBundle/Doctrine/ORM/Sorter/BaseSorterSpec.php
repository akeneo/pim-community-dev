<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class BaseSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder, CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeSorterInterface');
    }

    function it_adds_a_sorter_in_the_query(QueryBuilder $queryBuilder, AbstractAttribute $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $queryBuilder->getDQLPart('join')->willReturn([]);
        $queryBuilder->resetDQLPart('join')->shouldBeCalled();

        $condition = "sorterVsku.attribute = 42";
        $queryBuilder->leftJoin('p.values', 'sorterVsku', 'WITH', $condition)->shouldBeCalled();
        $queryBuilder->addOrderBy('sorterVsku.varchar', 'DESC')->shouldBeCalled();

        $queryBuilder->getRootAlias()->willReturn('p');
        $queryBuilder->addOrderBy("p.id")->shouldBeCalled();

        $this->addAttributeSorter($sku, 'DESC');
    }
}
