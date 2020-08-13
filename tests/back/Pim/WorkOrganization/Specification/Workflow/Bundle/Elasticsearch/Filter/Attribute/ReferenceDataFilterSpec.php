<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ReferenceDataFilter;

class ReferenceDataFilterSpec extends ObjectBehavior
{
    function let(
        ProposalAttributePathResolver $attributePathResolver,
        ReferenceDataRepositoryResolver $referenceDataRepositoryResolver,
        ConfigurationRegistryInterface $registry
    ) {
        $this->beConstructedWith(
            $attributePathResolver,
            $referenceDataRepositoryResolver,
            $registry,
            ['pim_reference_data_simpleselect', 'pim_reference_data_multiselect'],
            ['IN', 'EMPTY', 'NOT EMPTY', 'NOT IN']
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
                Operators::IN_LIST,
                Operators::IS_EMPTY,
                Operators::IS_NOT_EMPTY,
                Operators::NOT_IN_LIST,
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
        $registry->get('color')->willReturn($colorConfiguration);
        $registry->get('tags')->willReturn(null);
        $registry->get('brands')->willReturn(null);

        $color->getReferenceDataName()->willReturn('color');
        $tags->getReferenceDataName()->willReturn('tags');
        $brands->getReferenceDataName()->willReturn(null);

        $this->supportsAttribute($color)->shouldReturn(true);
        $this->supportsAttribute($tags)->shouldReturn(false);
        $this->supportsAttribute($brands)->shouldReturn(false);
    }

    function it_adds_a_filter_on_reference_data_with_operator_empty(
        $attributePathResolver,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color_attribute-reference_data_option.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.color_attribute-reference_data_option.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['color_attribute']]],
                        ['terms' => ['attributes_of_ancestors' => ['color_attribute']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_EMPTY, []);
    }

    function it_adds_a_filter_on_reference_data_with_operator_in_list(
        $attributePathResolver,
        $referenceDataRepositoryResolver,
        ReferenceDataRepositoryInterface $repository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getReferenceDataName()->willReturn('color_reference_data');
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $referenceDataRepositoryResolver->resolve('color_reference_data')->willReturn($repository);
        $repository->findCodesByIdentifiers(['black'])->willReturn([['code' => 'black']]);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color_attribute-reference_data_option.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['values.color_attribute-reference_data_option.ecommerce.en_US' => ['black']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IN_LIST, ['black']);
    }

    function it_adds_a_filter_on_reference_data_with_operator_is_not_empty(
        $attributePathResolver,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color_attribute-reference_data_option.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.color_attribute-reference_data_option.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_NOT_EMPTY, []);
    }

    function it_adds_a_filter_with_operator_not_in_list(
        $referenceDataRepositoryResolver,
        $attributePathResolver,
        ReferenceDataRepositoryInterface $repository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getReferenceDataName()->willReturn('color_reference_data');
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $referenceDataRepositoryResolver->resolve('color_reference_data')->willReturn($repository);
        $repository->findCodesByIdentifiers(['black'])->willReturn([['code' => 'black']]);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color_attribute-reference_data_option.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['values.color_attribute-reference_data_option.ecommerce.en_US' => ['black']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.color_attribute-reference_data_option.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::NOT_IN_LIST, ['black']);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $color)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, ['black']]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array(
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

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
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

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
        $attributePathResolver,
        ReferenceDataRepositoryInterface $repository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getReferenceDataName()->willReturn('color_reference_data');
        $color->getCode()->willReturn('color_attribute');
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION);

        $referenceDataRepositoryResolver->resolve('color_reference_data')->willReturn($repository);
        $repository->findCodesByIdentifiers(['black'])->willReturn([['code' => 'black']]);

        $this->setQueryBuilder($sqb);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color_attribute-reference_data_option.ecommerce.en_US']);

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

    function it_throws_an_execption_when_an_option_not_exists(
        $referenceDataRepositoryResolver,
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
