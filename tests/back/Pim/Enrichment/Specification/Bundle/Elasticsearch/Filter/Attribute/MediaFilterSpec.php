<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\MediaFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class MediaFilterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            ['pim_catalog_file', 'pim_catalog_image'],
            ['STARTS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', '!=', 'EMPTY', 'NOT EMPTY']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'STARTS WITH',
            'CONTAINS',
            'DOES NOT CONTAIN',
            '=',
            '!=',
            'EMPTY',
            'NOT EMPTY',
        ]);
        $this->supportsOperator('STARTS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_starts_with(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                    'query'         => 'sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::STARTS_WITH, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_contains(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::CONTAINS, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_does_not_contain(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.an_image-media.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::DOES_NOT_CONTAIN, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'term' => [
                    'values.an_image-media.ecommerce.en_US.original_filename' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::EQUALS, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'term' => [
                    'values.an_image-media.ecommerce.en_US.original_filename' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => ['field' => 'values.an_image-media.ecommerce.en_US'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::NOT_EQUAL, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_empty(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.an_image-media.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(['exists' => ['field' => 'family.code']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.an_image-media.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $name)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'an_image',
                MediaFilter::class,
                123
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                MediaFilter::class
            )
        )->during('addAttributeFilter', [$name, Operators::IN_CHILDREN_LIST, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');
        $name->isLocaleSpecific()->willReturn(true);
        $name->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "name" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($name, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'an_image',
                MediaFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');
        $name->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "name" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($name, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'an_image',
                MediaFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }
}
