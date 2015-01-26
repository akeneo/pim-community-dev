<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class PriceFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, CurrencyManager $currencyManager, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $currencyManager,
            ['pim_catalog_price_collection'],
            ['<', '<=', '=', '>=', '>', 'EMPTY']
        );
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['<', '<=', '=', '>=', '>', 'EMPTY']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_equals_filter_in_the_query(
        $attrValidatorHelper,
        $currencyManager,
        Builder $queryBuilder,
        AttributeInterface $price
    ) {
        $attrValidatorHelper->validateLocale($price, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($price, Argument::any())->shouldBeCalled();

        $value = ['data' => 22.5, 'currency' => 'EUR'];
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->equals(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '=', $value, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_filter_in_the_query(
        $attrValidatorHelper,
        $currencyManager,
        Builder $queryBuilder,
        AttributeInterface $price
    ) {
        $attrValidatorHelper->validateLocale($price, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($price, Argument::any())->shouldBeCalled();

        $value = ['data' => 22.5, 'currency' => 'EUR'];
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->gt(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '>', $value, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_or_equals_filter_in_the_query(
        $currencyManager,
        $attrValidatorHelper,
        Builder $queryBuilder,
        AttributeInterface $price
    ) {
        $attrValidatorHelper->validateLocale($price, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($price, Argument::any())->shouldBeCalled();

        $value = ['data' => 22.5, 'currency' => 'EUR'];
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->gte(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '>=', $value, 'en_US', 'mobile');
    }

    function it_adds_a_less_than_filter_in_the_query(
        $attrValidatorHelper,
        $currencyManager,
        Builder $queryBuilder,
        AttributeInterface $price
    ) {
        $attrValidatorHelper->validateLocale($price, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($price, Argument::any())->shouldBeCalled();

        $value = ['data' => 22.5, 'currency' => 'EUR'];
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->lt(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '<', $value, 'en_US', 'mobile');
    }

    function it_adds_a_less_than_or_equals_filter_in_the_query(
        $attrValidatorHelper,
        $currencyManager,
        Builder $queryBuilder,
        AttributeInterface $price
    ) {
        $attrValidatorHelper->validateLocale($price, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($price, Argument::any())->shouldBeCalled();

        $value = ['data' => 22.5, 'currency' => 'EUR'];
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $queryBuilder->field('normalizedData.price-en_US-mobile.EUR.data')->willReturn($queryBuilder);
        $queryBuilder->lte(22.5)->willReturn($queryBuilder);

        $this->addAttributeFilter($price, '<=', $value, 'en_US', 'mobile');
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

    function it_throws_an_exception_if_value_had_not_a_valid_currency($currencyManager, AttributeInterface $attribute)
    {
        $currencyManager->getActiveCodes()->willReturn(['EUR', 'USD']);

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
