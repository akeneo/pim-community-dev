<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\NumberFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;

class NumberFilterSpec extends ObjectBehavior
{
    function let(ProposalAttributePathResolver $attributePathResolver)
    {
        $this->beConstructedWith(
            $attributePathResolver,
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
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.size-decimal.ecommerce.en_US' => ['lt' => 10]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::LOWER_THAN, 10);
    }

    function it_adds_a_filter_with_operator_lower_or_equal_than(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.size-decimal.ecommerce.en_US' => ['lte' => 10]]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::LOWER_OR_EQUAL_THAN, 10);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.size-decimal.ecommerce.en_US' => 10]],
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::EQUALS, 10);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.size-decimal.ecommerce.en_US' => 10]],
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.size-decimal.ecommerce.en_US']],
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::NOT_EQUAL, 10);
    }

    function it_adds_a_filter_with_operator_greater_or_equal_than(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.size-decimal.ecommerce.en_US' => ['gte' => 10]]],
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::GREATER_OR_EQUAL_THAN, 10);
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.size-decimal.ecommerce.en_US' => ['gt' => 10]]],
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::GREATER_THAN, 10);
    }

    function it_adds_a_filter_with_operator_is_empty(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.size-decimal.ecommerce.en_US']],
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['size']]],
                        ['terms' => ['attributes_of_ancestors' => ['size']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::IS_EMPTY, null);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.size-decimal.ecommerce.en_US']],
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($size, Operators::IS_NOT_EMPTY, null);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $size)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$size, Operators::NOT_EQUAL, 10]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_numeric(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::numericExpected(
                'size',
                NumberFilter::class,
                'NOT_NUMERIC'
            )
        )->during('addAttributeFilter', [$size, Operators::LOWER_THAN, 'NOT_NUMERIC']);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributePathResolver,
        AttributeInterface $size,
        SearchQueryBuilder $sqb
    ) {
        $size->getCode()->willReturn('size');
        $size->getBackendType()->willReturn('decimal');

        $attributePathResolver->getAttributePaths($size)->willReturn(['values.size-decimal.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                NumberFilter::class
            )
        )->during('addAttributeFilter', [$size, Operators::IN_CHILDREN_LIST, 10]);
    }
}
