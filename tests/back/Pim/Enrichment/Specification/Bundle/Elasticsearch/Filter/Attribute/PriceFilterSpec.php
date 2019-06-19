<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\PriceFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class PriceFilterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper, CurrencyRepositoryInterface $currencyRepository)
    {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            $currencyRepository,
            ['pim_catalog_price_collection'],
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PriceFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                '<',
                '<=',
                Operators::EQUALS,
                '>=',
                '>',
                'EMPTY',
                'NOT EMPTY',
                '!=',
            ]
        );
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_price_collection_attribute(AttributeInterface $price, AttributeInterface $tags)
    {
        $price->getType()->willReturn('pim_catalog_price_collection');
        $tags->getType()->willReturn('pim_catalog_multiselect');

        $this->getAttributeTypes()->shouldReturn(
            [
                'pim_catalog_price_collection',
            ]
        );

        $this->supportsAttribute($price)->shouldReturn(true);
        $this->supportsAttribute($tags)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_lower_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.ecommerce.en_US.USD' => ['lt' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::LOWER_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_lower_or_equal_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.ecommerce.en_US.USD' => ['lte' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_equals(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'term' => [
                    'values.a_price-prices.ecommerce.en_US.USD' => 10,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::EQUALS,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'term' => [
                    'values.a_price-prices.ecommerce.en_US.USD' => 10,
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.ecommerce.en_US.USD',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::NOT_EQUAL,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_greater_or_equal_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.ecommerce.en_US.USD' => ['gte' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.a_price-prices.ecommerce.en_US.USD' => ['gt' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::GREATER_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_empty_on_all_currencies(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(['exists' => ['field' => 'family.code']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($price, Operators::IS_EMPTY, [], 'en_US', 'ecommerce', []);
        $this->addAttributeFilter($price, Operators::IS_EMPTY_ON_ALL_CURRENCIES, [], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_empty_for_currency(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.ecommerce.en_US.USD',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_EMPTY_FOR_CURRENCY,
            ['currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_not_empty_on_at_least_one_currency(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($price, Operators::IS_NOT_EMPTY, [], 'en_US', 'ecommerce', []);
        $this->addAttributeFilter($price, Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY, [], 'en_US', 'ecommerce',
            []);
    }

    function it_adds_a_filter_with_operator_is_not_empty_for_currency(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.a_price-prices.ecommerce.en_US.USD',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_NOT_EMPTY_FOR_CURRENCY,
            ['currency' => 'USD'],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_throws_an_exception_if_the_value_is_not_an_array(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter()->shouldNotBeCalled();

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'a_price',
                PriceFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, 'NOT_AN_ARRAY', 'en_US', 'ecommerce']
        );
    }

    function it_throws_if_the_value_array_is_not_expected(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter()->shouldNotBeCalled();

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'a_price',
                'amount',
                PriceFilter::class,
                []
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, [], 'en_US', 'ecommerce']
        );

        $this->shouldThrow(
            InvalidPropertyTypeException::numericExpected(
                'a_price',
                sprintf('key "amount" has to be a numeric, "%s" given', gettype('NOT_AN_AMOUNT')),
                ['amount' => 'NOT_AN_AMOUNT']
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => 'NOT_AN_AMOUNT'], 'en_US', 'ecommerce']
        );

        $this->shouldThrow(
            InvalidPropertyTypeException::numericExpected(
                'a_price',
                sprintf('key "amount" has to be a numeric, "%s" given', gettype(null)),
                ['amount' => null]
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => null], 'en_US', 'ecommerce']
        );
    }

    function it_throws_if_the_currency_is_not_supported(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['USD']);
        $price->getCode()->willReturn('a_price');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter()->shouldNotBeCalled();

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'a_price',
                'currency',
                PriceFilter::class,
                ['amount' => 12]
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => 12], 'en_US', 'ecommerce']
        );

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'a_price',
                'currency',
                'The currency does not exist',
                PriceFilter::class,
                'YEN'
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => 12, 'currency' => 'YEN'], 'en_US', 'ecommerce']
        );

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'a_price',
                'currency',
                'The currency does not exist',
                PriceFilter::class,
                2
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => 12, 'currency' => 2], 'en_US', 'ecommerce']
        );

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'a_price',
                'currency',
                'The currency does not exist',
                PriceFilter::class,
                ''
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => 12, 'currency' => ''], 'en_US', 'ecommerce']
        );

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'a_price',
                'currency',
                'The currency does not exist',
                PriceFilter::class,
                null
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => 12, 'currency' => null], 'en_US', 'ecommerce']
        );
    }

    function it_throws_an_exception_if_no_currency_is_provided_for_operator_empty_for_currency(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter()->shouldNotBeCalled();

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'a_price',
                'currency',
                PriceFilter::class,
                []
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::IS_EMPTY_FOR_CURRENCY, [], 'en_US', 'ecommerce']
        );
    }

    function it_throws_an_exception_if_no_currency_is_provided_for_operator_not_empty_for_currency(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter()->shouldNotBeCalled();

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'a_price',
                'currency',
                PriceFilter::class,
                []
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::IS_NOT_EMPTY_FOR_CURRENCY, [], 'en_US', 'ecommerce']
        );
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributeValidatorHelper->validateLocale($price, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($price, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                PriceFilter::class
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::IN_CHILDREN_LIST, ['amount' => 10, 'currency' => 'USD'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocaleSpecific()->willReturn(true);
        $price->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "prices" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($price, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'a_price',
                PriceFilter::class,
                $e
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10, 'currency' => 'USD'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');
        $price->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "a_price" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($price, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'a_price',
                PriceFilter::class,
                $e
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10, 'currency' => 'USD'], 'en_US', 'ecommerce', []]
        );
    }
}
