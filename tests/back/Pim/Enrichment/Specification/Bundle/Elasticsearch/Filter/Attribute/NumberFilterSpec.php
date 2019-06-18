<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\NumberFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class NumberFilterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            ['pim_catalog_number'],
            ['<', '<=', '=', '>=', '>', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NumberFilter::class);
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

    function it_supports_number_attribute(AttributeInterface $size, AttributeInterface $tags)
    {
        $size->getType()->willReturn('pim_catalog_number');
        $tags->getType()->willReturn('pim_catalog_multiselect');

        $this->getAttributeTypes()->shouldReturn(
            [
                'pim_catalog_number',
            ]
        );

        $this->supportsAttribute($size)->shouldReturn(true);
        $this->supportsAttribute($tags)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_lower_than(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.size-decimal.ecommerce.en_US' => ['lt' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::LOWER_THAN, 10, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_lower_or_equal_than(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.size-decimal.ecommerce.en_US' => ['lte' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::LOWER_OR_EQUAL_THAN, 10, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'term' => [
                    'values.size-decimal.ecommerce.en_US' => 10,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::EQUALS, 10, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'term' => [
                    'values.size-decimal.ecommerce.en_US' => 10,
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.size-decimal.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::NOT_EQUAL, 10, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_greater_or_equal_than(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.size-decimal.ecommerce.en_US' => ['gte' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::GREATER_OR_EQUAL_THAN, 10, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.size-decimal.ecommerce.en_US' => ['gt' => 10],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::GREATER_THAN, 10, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_empty(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.size-decimal.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(['exists' => ['field' => 'family.code']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::IS_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.size-decimal.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $size)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$size, Operators::NOT_EQUAL, 10, 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_numeric(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::numericExpected(
                'size',
                NumberFilter::class,
                'NOT_NUMERIC'
            )
        )->during('addAttributeFilter', [$size, Operators::LOWER_THAN, 'NOT_NUMERIC', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributeValidatorHelper->validateLocale($size, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($size, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                NumberFilter::class
            )
        )->during('addAttributeFilter', [$size, Operators::IN_CHILDREN_LIST, 10, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');
        $size->isLocaleSpecific()->willReturn(true);
        $size->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "size" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($size, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'size',
                NumberFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$size, Operators::CONTAINS, 10, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');
        $size->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "size" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($size, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'size',
                NumberFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$size, Operators::NOT_EQUAL, 10, 'en_US', 'ecommerce', []]);
    }
}
