<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\PriceFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;

class PriceFilterSpec extends ObjectBehavior
{
    function let(ProposalAttributePathResolver $attributePathResolver, CurrencyRepositoryInterface $currencyRepository)
    {
        $this->beConstructedWith(
            $attributePathResolver,
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
                '=',
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
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.a_price-prices.ecommerce.en_US.USD' => ['lt' => 10]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::LOWER_THAN,
            ['amount' => 10, 'currency' => 'USD']
        );
    }

    function it_adds_a_filter_with_operator_lower_or_equal_than(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.a_price-prices.ecommerce.en_US.USD' => ['lte' => 10]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 10, 'currency' => 'USD']
        );
    }

    function it_adds_a_filter_with_operator_equals(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.a_price-prices.ecommerce.en_US.USD' => 10]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::EQUALS,
            ['amount' => 10, 'currency' => 'USD']
        );
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.a_price-prices.ecommerce.en_US.USD' => 10]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.a_price-prices.ecommerce.en_US.USD']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::NOT_EQUAL,
            ['amount' => 10, 'currency' => 'USD']
        );
    }

    function it_adds_a_filter_with_operator_greater_or_equal_than(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.a_price-prices.ecommerce.en_US.USD' => ['gte' => 10]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => 10, 'currency' => 'USD']
        );
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.a_price-prices.ecommerce.en_US.USD' => ['gt' => 10]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::GREATER_THAN,
            ['amount' => 10, 'currency' => 'USD']
        );
    }

    function it_adds_a_filter_with_operator_is_empty_on_all_currencies(
        $attributePathResolver,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.a_price-prices.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($price, Operators::IS_EMPTY, [], 'en_US', 'ecommerce', []);
        $this->addAttributeFilter($price, Operators::IS_EMPTY_ON_ALL_CURRENCIES, [], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_empty_for_currency(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.a_price-prices.ecommerce.en_US.USD']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_EMPTY_FOR_CURRENCY,
            ['currency' => 'USD']
        );
    }

    function it_adds_a_filter_with_operator_is_not_empty_on_at_least_one_currency(
        $attributePathResolver,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.a_price-prices.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($price, Operators::IS_NOT_EMPTY, [], 'en_US', 'ecommerce', []);
        $this->addAttributeFilter($price, Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY, [], 'en_US', 'ecommerce',
            []);
    }

    function it_adds_a_filter_with_operator_is_not_empty_for_currency(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.a_price-prices.ecommerce.en_US.USD']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $price,
            Operators::IS_NOT_EMPTY_FOR_CURRENCY,
            ['currency' => 'USD']
        );
    }

    function it_throws_an_exception_if_the_value_is_not_an_array(
        $attributePathResolver,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

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
                PriceFilter::class,
                'NOT_AN_AMOUNT'
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => 'NOT_AN_AMOUNT'], 'en_US', 'ecommerce']
        );

        $this->shouldThrow(
            InvalidPropertyTypeException::numericExpected(
                'a_price',
                PriceFilter::class,
                null
            )
        )->during(
            'addAttributeFilter',
            [$price, Operators::EQUALS, ['amount' => null], 'en_US', 'ecommerce']
        );
    }

    function it_throws_if_the_currency_is_not_supported(
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['USD']);
        $price->getCode()->willReturn('a_price');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

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
        $attributePathResolver,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $price->getCode()->willReturn('a_price');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

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
        $attributePathResolver,
        $currencyRepository,
        AttributeInterface $price,
        SearchQueryBuilder $sqb
    ) {
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['USD']);
        $price->getCode()->willReturn('a_price');
        $price->getBackendType()->willReturn('prices');

        $attributePathResolver->getAttributePaths($price)->willReturn(['values.a_price-prices.ecommerce.en_US']);

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
}
