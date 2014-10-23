<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class StringFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith(['pim_catalog_identifier'], [], ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN']);
        $this->supportsOperator('ENDS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_starts_with_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filtersku.attribute = 42 AND filtersku.varchar LIKE '%My Sku'";

        $queryBuilder->innerJoin('p.values', 'filtersku', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($sku, 'STARTS WITH', 'My Sku');
    }

    function it_adds_a_ends_with_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filtersku.attribute = 42 AND filtersku.varchar LIKE 'My Sku%'";

        $queryBuilder->innerJoin('p.values', 'filtersku', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($sku, 'ENDS WITH', 'My Sku');
    }

    function it_adds_a_contains_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filtersku.attribute = 42 AND filtersku.varchar LIKE '%My Sku%'";

        $queryBuilder->innerJoin('p.values', 'filtersku', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($sku, 'CONTAINS', 'My Sku');
    }

    function it_adds_a_does_not_contain_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filtersku.attribute = 42 AND filtersku.varchar NOT LIKE '%My Sku%'";

        $queryBuilder->innerJoin('p.values', 'filtersku', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($sku, 'DOES NOT CONTAIN', 'My Sku');
    }
}
