<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr\Comparison;
use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class PriceFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith(['pim_catalog_price_collection'], ['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_equals_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $price)
    {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $condition = "filterprice.attribute = 42";
        $queryBuilder->innerJoin('p.values', 'filterprice', 'WITH', $condition)->shouldBeCalled();

        $condition = "filterPprice.currency = 'EUR' AND filterPprice.data = '12'";
        $queryBuilder->innerJoin('filterprice.prices', 'filterPprice', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($price, '=', '12 EUR');
    }

    function it_adds_a_greater_than_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $price)
    {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $condition = "filterprice.attribute = 42";
        $queryBuilder->innerJoin('p.values', 'filterprice', 'WITH', $condition)->shouldBeCalled();

        $condition = "filterPprice.currency = 'EUR' AND filterPprice.data > '12'";
        $queryBuilder->innerJoin('filterprice.prices', 'filterPprice', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($price, '>', '12 EUR');
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $price)
    {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $condition = "filterprice.attribute = 42";
        $queryBuilder->innerJoin('p.values', 'filterprice', 'WITH', $condition)->shouldBeCalled();

        $condition = "filterPprice.currency = 'EUR' AND filterPprice.data >= '12'";
        $queryBuilder->innerJoin('filterprice.prices', 'filterPprice', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($price, '>=', '12 EUR');
    }

    function it_adds_a_less_than_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $price)
    {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $condition = "filterprice.attribute = 42";
        $queryBuilder->innerJoin('p.values', 'filterprice', 'WITH', $condition)->shouldBeCalled();

        $condition = "filterPprice.currency = 'EUR' AND filterPprice.data < '12'";
        $queryBuilder->innerJoin('filterprice.prices', 'filterPprice', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($price, '<', '12 EUR');
    }

    function it_adds_a_less_than_or_equals_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $price)
    {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $condition = "filterprice.attribute = 42";
        $queryBuilder->innerJoin('p.values', 'filterprice', 'WITH', $condition)->shouldBeCalled();

        $condition = "filterPprice.currency = 'EUR' AND filterPprice.data <= '12'";
        $queryBuilder->innerJoin('filterprice.prices', 'filterPprice', 'WITH', $condition)->shouldBeCalled();

        $this->addAttributeFilter($price, '<=', '12 EUR');
    }

    function it_checks_if_attribute_is_supported(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->shouldBeCalled()->willReturn('pim_catalog_price_collection');
        $this->supportsAttribute($attribute)->shouldReturn(true);
    }

    function it_adds_an_empty_filter_in_the_query(QueryBuilder $queryBuilder, AttributeInterface $price, Expr $expr, Comparison $comparison)
    {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $condition = "filterprice.attribute = 42";
        $queryBuilder->leftJoin('p.values', 'filterprice', 'WITH', $condition)->shouldBeCalled();

        $queryBuilder->leftJoin('filterprice.prices', 'filterPprice')->shouldBeCalled();

        $queryBuilder->expr()->willReturn($expr);

        $expr->literal('')->shouldBeCalled()->willReturn('ok');
        $expr->eq('filterPprice.currency', 'ok')->willReturn($comparison);
        $expr->isNull('filterPprice.data')->shouldBeCalled()->willReturn('filterPprice.data IS NULL');
        $expr->isNull('filterPprice.id')->shouldBeCalled()->willReturn('filterPprice.id IS NULL');
        $expr->orX(' AND filterPprice.data IS NULL', 'filterPprice.id IS NULL')->shouldBeCalled();
        $queryBuilder->andWhere(null)->shouldBeCalled();

        $this->addAttributeFilter($price, 'EMPTY', ' ');
    }

    function it_throws_an_exception_if_value_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attributeCode');
        $this->shouldThrow(InvalidArgumentException::stringExpected('attributeCode', 'filter', 'price'))->during('addAttributeFilter', [$attribute, '=', 123]);
    }
}
