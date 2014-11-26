<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class StringFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith(['pim_catalog_identifier'], ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN']);
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

    function it_adds_a_starts_with_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
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

        $this->addAttributeFilter($sku, 'STARTS WITH', 'My Sku');
    }

    function it_adds_a_ends_with_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
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

        $this->addAttributeFilter($sku, 'ENDS WITH', 'My Sku');
    }

    function it_adds_a_contains_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
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

    function it_adds_a_does_not_contain_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
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

    function it_adds_a_equal_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filtersku.attribute = 42 AND filtersku.varchar = 'My Sku'";

        $queryBuilder->innerJoin('p.values', 'filtersku', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($sku, '=', 'My Sku');
    }

    function it_adds_an_empty_attribute_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');
        $condition = "filtersku.attribute = 42";

        $queryBuilder->leftJoin('p.values', 'filtersku', 'WITH', $condition)->shouldBeCalled();
        $queryBuilder->andWhere('filtersku.varchar IS NULL')->shouldBeCalled();

        $this->addAttributeFilter($sku, 'EMPTY', 'My Sku');
    }

    function it_throws_an_exception_if_value_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');
        $this->shouldThrow(InvalidArgumentException::stringExpected('attributeCode', 'filter', 'string'))->during('addAttributeFilter', [$attribute, '=', 123]);
    }
}
