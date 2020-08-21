<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\TextAreaFilter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TextAreaFilterSpec extends ObjectBehavior
{
    function let(ProposalAttributePathResolver $attributePathResolver)
    {
        $this->beConstructedWith(
            $attributePathResolver,
            ['pim_catalog_textarea'],
            ['STARTS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN', 'EMPTY', 'NOT EMPTY']
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
                'NOT EMPTY'
            ]
        );
        $this->supportsOperator('STARTS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.description-textarea.ecommerce.en_US.preprocessed' => 'Sony']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::EQUALS, 'Sony');
    }

    function it_adds_a_filter_with_operator_empty(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.description-textarea.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
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
        $this->addAttributeFilter($description, Operators::IS_EMPTY, null);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.description-textarea.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::IS_NOT_EMPTY, null);
    }

    function it_adds_a_filter_with_operator_contains(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.description-textarea.ecommerce.en_US.preprocessed',
                                'query'         => '*sony*',
                            ],
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::CONTAINS, 'sony');
    }

    function it_adds_a_filter_with_operator_does_not_contain(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.description-textarea.ecommerce.en_US.preprocessed',
                                'query'         => '*sony*',
                            ],
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter([
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.description-textarea.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::DOES_NOT_CONTAIN, 'sony');
    }

    function it_adds_a_filter_with_operator_starts_with(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.description-textarea.ecommerce.en_US.preprocessed',
                                'query'         => 'sony*',
                            ],
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($description, Operators::STARTS_WITH, 'sony');
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $description)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$description, Operators::CONTAINS, 'Sony']);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'description',
                TextAreaFilter::class,
                123
            )
        )->during('addAttributeFilter', [$description, Operators::CONTAINS, 123]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributePathResolver,
        AttributeInterface $description,
        SearchQueryBuilder $sqb
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');

        $attributePathResolver->getAttributePaths($description)->willReturn(['values.description-textarea.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                TextAreaFilter::class
            )
        )->during('addAttributeFilter', [$description, Operators::IN_CHILDREN_LIST, 'Sony']);
    }

}
