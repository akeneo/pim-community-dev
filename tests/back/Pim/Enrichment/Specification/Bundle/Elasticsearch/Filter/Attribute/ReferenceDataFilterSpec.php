<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\ReferenceDataFilter;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;

class ReferenceDataFilterSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attributeValidatorHelper,
        ReferenceDataRepositoryResolver $referenceDataRepositoryResolver,
        ConfigurationRegistryInterface $registry
    ) {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            $referenceDataRepositoryResolver,
            $registry,
            ['pim_reference_data_simpleselect', 'pim_reference_data_multiselect'],
            ['IN', 'EMPTY', 'NOT_EMPTY', 'NOT IN']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'IN',
                'EMPTY',
                'NOT_EMPTY',
                'NOT IN',
            ]
        );
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_attribute_reference_data(
        $registry,
        ReferenceDataConfigurationInterface $colorConfiguration,
        AttributeInterface $color,
        AttributeInterface $tags,
        AttributeInterface $brands
    ) {
        $registry->has('color')->willReturn(true);
        $registry->has('tags')->willReturn(false);
        $registry->has('brands')->willReturn(false);

        $color->getReferenceDataName()->willReturn('color');
        $tags->getReferenceDataName()->willReturn('tags');
        $brands->getReferenceDataName()->willReturn(null);

        $this->supportsAttribute($color)->shouldReturn(true);
        $this->supportsAttribute($tags)->shouldReturn(false);
        $this->supportsAttribute($brands)->shouldReturn(false);
    }

    function it_adds_a_filter_on_reference_data_with_operator_empty(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'values.color_attribute-reference_data_option.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(['exists' => ['field' => 'family.code']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_on_reference_data_with_operator_in_list(
        $referenceDataRepositoryResolver,
        $attributeValidatorHelper,
        ReferenceDataRepositoryInterface $repository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getReferenceDataName()->willReturn('color_reference_data');
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $referenceDataRepositoryResolver->resolve('color_reference_data')->willReturn($repository);
        $repository->findCodesByIdentifiers(['black'])->willReturn([['code' => 'black']]);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'terms' => [
                    'values.color_attribute-reference_data_option.ecommerce.en_US' => ['black'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IN_LIST, ['black'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_on_reference_data_with_operator_is_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => "values.color_attribute-reference_data_option.ecommerce.en_US",
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_in_list(
        $referenceDataRepositoryResolver,
        $attributeValidatorHelper,
        ReferenceDataRepositoryInterface $repository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getReferenceDataName()->willReturn('color_reference_data');
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $referenceDataRepositoryResolver->resolve('color_reference_data')->willReturn($repository);
        $repository->findCodesByIdentifiers(['black'])->willReturn([['code' => 'black']]);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'terms' => [
                    'values.color_attribute-reference_data_option.ecommerce.en_US' => ['black'],
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.color_attribute-reference_data_option.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $color)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'color_attribute',
                ReferenceDataFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'color_attribute',
                ReferenceDataFilter::class,
                true
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, [true], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $referenceDataRepositoryResolver,
        $attributeValidatorHelper,
        ReferenceDataRepositoryInterface $repository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getReferenceDataName()->willReturn('color_reference_data');
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $referenceDataRepositoryResolver->resolve('color_reference_data')->willReturn($repository);
        $repository->findCodesByIdentifiers(['black'])->willReturn([['code' => 'black']]);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                ReferenceDataFilter::class
            )
        )->during(
            'addAttributeFilter',
            [$color, Operators::IN_CHILDREN_LIST, ['black'], 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);
        $color->isLocaleSpecific()->willReturn(true);
        $color->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "color" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($color, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'color_attribute',
                ReferenceDataFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);
        $color->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "color_attribute" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($color, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'color_attribute',
                ReferenceDataFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_execption_when_an_option_not_exists(
        $referenceDataRepositoryResolver,
        $attributeValidatorHelper,
        ReferenceDataRepositoryInterface $repository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getReferenceDataName()->willReturn('color_reference_data');
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $referenceDataRepositoryResolver->resolve('color_reference_data')->willReturn($repository);
        $repository->findCodesByIdentifiers(['black'])->willReturn([]);
        $repository->getClassName()->willReturn('My\Class');

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'color_attribute',
                'code',
                'No reference data "color_reference_data" with code "black" has been found',
                'My\Class',
                'black'
            )
        )->during('addAttributeFilter', [$color, Operators::IN_CHILDREN_LIST, ['black'], 'en_US', 'ecommerce', []]);
    }
}
