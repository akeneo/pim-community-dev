<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\TextFilter;
use PhpSpec\ObjectBehavior;

class TextFilterSpec extends ObjectBehavior
{
    function let(ProposalAttributePathResolver $attributePathResolver)
    {
        $this->beConstructedWith(
            $attributePathResolver,
            ['pim_catalog_text'],
            ['STARTS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN', 'EMPTY', 'NOT EMPTY']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextFilter::class);
    }

    function it_is_a_filter()
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
            'IN',
            'EMPTY',
            'NOT EMPTY'
        ]);
        $this->supportsOperator('STARTS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $sqb->addFilter([
            'bool' => [
                'should' => [
                    ['term' => ['values.name-text.ecommerce.en_US' => 'Sony']]
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::EQUALS, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_empty(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.name-text.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['name']]],
                        ['terms' => ['attributes_of_ancestors' => ['name']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.name-text.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_contains(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.name-text.ecommerce.en_US',
                                'query'         => '*sony*',
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::CONTAINS, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_does_not_contain(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.name-text.ecommerce.en_US',
                                'query'         => '*sony*',
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.name-text.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::DOES_NOT_CONTAIN, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_starts_with(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.name-text.ecommerce.en_US',
                                'query'         => 'sony*',
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::STARTS_WITH, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $name)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'name',
                TextFilter::class,
                123
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.name-text.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                TextFilter::class
            )
        )->during('addAttributeFilter', [$name, Operators::IN_CHILDREN_LIST, 'Sony', 'en_US', 'ecommerce', []]);
    }
}
