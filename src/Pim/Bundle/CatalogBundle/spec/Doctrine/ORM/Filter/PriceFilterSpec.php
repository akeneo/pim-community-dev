<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Prophecy\Argument;

class PriceFilterSpec extends ObjectBehavior
{
    function let(
        QueryBuilder $queryBuilder,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->beConstructedWith(
            $currencyRepository,
            ['pim_catalog_price_collection'],
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_equals_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $value = ['data' => 12, 'currency' => 'EUR'];
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $queryBuilder->innerJoin(Argument::any(), Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($price, '=', $value);
    }

    function it_adds_a_greater_than_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $value = ['data' => 12, 'currency' => 'EUR'];
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $queryBuilder->innerJoin(Argument::any(), Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($price, '>', $value);
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $value = ['data' => 12, 'currency' => 'EUR'];
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $queryBuilder->innerJoin(Argument::any(), Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($price, '>=', $value);
    }

    function it_adds_a_less_than_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $value = ['data' => 12, 'currency' => 'EUR'];
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $queryBuilder->innerJoin(Argument::any(), Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($price, '<', $value);
    }

    function it_adds_a_less_than_or_equals_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $value = ['data' => 12, 'currency' => 'EUR'];
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $queryBuilder->innerJoin(Argument::any(), Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $this->addAttributeFilter($price, '<=', $value);
    }

    function it_checks_if_attribute_is_supported(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $this->supportsAttribute($attribute)->shouldReturn(true);
    }

    function it_adds_an_empty_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price,
        Expr $expr,
        Comparison $comparison
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $value = ['data' => null, 'currency' => 'EUR'];
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $queryBuilder->leftJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $queryBuilder->leftJoin(Argument::any(), Argument::any())->shouldBeCalled();
        $queryBuilder->expr()->willReturn($expr);

        $expr->literal('EUR')->willReturn('EUR');
        $expr->eq(Argument::any(), 'EUR')->willReturn($comparison);
        $expr->isNull(Argument::any())->willReturn('filterPprice.data IS NOT NULL');
        $expr->isNull(Argument::any())->willReturn('filterPprice.id IS NOT NULL');
        $expr->orX(Argument::any(), Argument::any())->shouldBeCalled();
        $queryBuilder->andWhere(null)->shouldBeCalled();

        $this->addAttributeFilter($price, 'EMPTY', $value);
    }

    function it_adds_a_not_empty_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price,
        Expr $expr,
        Comparison $comparison
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $queryBuilder->expr()->willReturn(new Expr());
        $queryBuilder->getRootAlias()->willReturn('p');

        $value = ['data' => null, 'currency' => 'EUR'];
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $queryBuilder->leftJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();

        $queryBuilder->leftJoin(Argument::any(), Argument::any())->shouldBeCalled();
        $queryBuilder->expr()->willReturn($expr);

        $expr->literal('EUR')->willReturn('EUR');
        $expr->eq(Argument::any(), 'EUR')->willReturn($comparison);
        $expr->isNotNull(Argument::any())->shouldBeCalledTimes(2);
        $expr->andX(Argument::any(), Argument::any())->shouldBeCalled();
        $queryBuilder->andWhere(null)->shouldBeCalled();

        $this->addAttributeFilter($price, 'NOT EMPTY', $value);
    }

    function it_adds_a_not_equal_filter_in_the_query(
        $currencyRepository,
        QueryBuilder $queryBuilder,
        AttributeInterface $price,
        Expr $expr,
        Expr\Comparison $comp,
        Expr\Literal $currencyLiteral,
        Expr\Literal $dataLiteral
    ) {
        $price->getId()->willReturn(42);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);

        $value = ['data' => 12, 'currency' => 'EUR'];

        $queryBuilder->expr()->willReturn($expr);
        $queryBuilder->getRootAlias()->willReturn('p');
        $expr->literal('EUR')->willReturn($currencyLiteral);
        $expr->literal(12)->willReturn($dataLiteral);
        $expr->eq(Argument::any(), $currencyLiteral)->shouldBeCalled();
        $expr->neq(Argument::any(), $dataLiteral)->shouldBeCalled()->willReturn($comp);
        $currencyLiteral->__toString()->willReturn('EUR');
        $dataLiteral->__toString()->willReturn('12');
        $comp->__toString()->willReturn('filterPprice.data <> 12');

        $queryBuilder->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())->shouldBeCalled();
        $queryBuilder->innerJoin(
            Argument::containingString('.prices'),
            Argument::containingString('filterPprice'),
            'WITH',
            Argument::containingString('filterPprice.data <> 12')
        )->shouldBeCalled();

        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $this->addAttributeFilter($price, '!=', $value);
    }

    function it_throws_an_exception_if_value_is_not_an_valid_array(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('price_code');

        $value = ['currency' => 'foo'];
        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected('price_code', 'data', 'filter', 'price', print_r($value, true))
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['data' => 459];
        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'price_code',
                'currency',
                'filter',
                'price',
                print_r($value, true)
            )
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['data' => 'foo', 'currency' => 'foo'];
        $this->shouldThrow(
            InvalidArgumentException::arrayNumericKeyExpected('price_code', 'data', 'filter', 'price', 'string')
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);

        $value = ['data' => 132, 'currency' => 42];
        $this->shouldThrow(
            InvalidArgumentException::arrayStringKeyExpected('price_code', 'currency', 'filter', 'price', 'integer')
        )
            ->during('addAttributeFilter', [$attribute, '=', $value]);
    }

    function it_throws_an_exception_if_value_had_not_a_valid_currency(
        $currencyRepository,
        AttributeInterface $attribute
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $attribute->getCode()->willReturn('price_code');
        $value = ['data' => 132, 'currency' => 'FOO'];
        $this->shouldThrow(
            InvalidArgumentException::arrayInvalidKey(
                'price_code',
                'currency',
                'The currency does not exist',
                'filter',
                'price',
                'FOO'
            )
        )->during('addAttributeFilter', [$attribute, '=', $value]);
    }
}
