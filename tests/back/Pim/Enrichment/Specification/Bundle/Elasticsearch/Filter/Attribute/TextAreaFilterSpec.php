<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\TextAreaFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;

class TextAreaFilterSpec extends ObjectBehavior
{
    function let(ElasticsearchFilterValidator $filterValidator)
    {
        $this->beConstructedWith(
            $filterValidator,
            ['pim_catalog_textarea'],
            ['STARTS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextAreaFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'STARTS WITH',
                'CONTAINS',
                'DOES NOT CONTAIN',
                '=',
                'IN',
                'EMPTY',
                'NOT EMPTY',
                '!=',
            ]
        );
        $this->supportsOperator('STARTS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'match_phrase' => [
                    'values.description-textarea.ecommerce.en_US.preprocessed' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::EQUALS, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'match_phrase' => [
                    'values.description-textarea.ecommerce.en_US.preprocessed' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => ['field' => 'values.description-textarea.ecommerce.en_US.preprocessed'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::NOT_EQUAL, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_empty(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.description-textarea.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['description']]],
                        ['terms' => ['attributes_of_ancestors' => ['description']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::IS_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.description-textarea.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_contains(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'values.description-textarea.ecommerce.en_US.preprocessed',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::CONTAINS, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_does_not_contain(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'values.description-textarea.ecommerce.en_US.preprocessed',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter([
                'exists' => [
                    'field' => 'values.description-textarea.ecommerce.en_US.preprocessed',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::DOES_NOT_CONTAIN, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_starts_with(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'values.description-textarea.ecommerce.en_US.preprocessed',
                    'query'         => 'sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::STARTS_WITH, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $description)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$description, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'description',
                TextAreaFilter::class,
                123
            )
        )->during('addAttributeFilter', [$description, Operators::CONTAINS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_null(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'description',
                TextAreaFilter::class,
                null
            )
        )->during('addAttributeFilter', [$description, Operators::CONTAINS, null, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $filterValidator->validateLocaleForAttribute('description', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('description', 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                TextAreaFilter::class
            )
        )->during('addAttributeFilter', [$description, Operators::IN_CHILDREN_LIST, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');
        $description->isLocaleSpecific()->willReturn(true);
        $description->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "description" expects a locale, none given.');
        $filterValidator->validateLocaleForAttribute('description', 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'description',
                TextAreaFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$description, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $filterValidator,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');
        $description->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "description" does not expect a scope, "ecommerce" given.');
        $filterValidator->validateLocaleForAttribute('description', 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'description',
                TextAreaFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$description, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }
}
