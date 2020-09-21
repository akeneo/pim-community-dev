<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\MediaFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;

class MediaFilterSpec extends ObjectBehavior
{
    function let(ProposalAttributePathResolver $attributePathResolver, AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith(
            $attributePathResolver,
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
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                                'query'         => 'sony*',
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::STARTS_WITH, 'sony');
    }

    function it_adds_a_filter_with_operator_contains(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                                'query'         => '*sony*',
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::CONTAINS, 'sony');
    }

    function it_adds_a_filter_with_operator_does_not_contain(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);
        $sqb->addMustNot([
            'bool' => [
                'should' => [
                    [
                        'query_string' => [
                            'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                            'query'         => '*sony*',
                        ]
                    ]
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $sqb->addFilter([
            'bool' => [
                'should' => [
                    ['exists' => ['field' => 'values.an_image-media.ecommerce.en_US']]
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::DOES_NOT_CONTAIN, 'sony');
    }

    function it_adds_a_filter_with_operator_equals(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'term' => [
                                'values.an_image-media.ecommerce.en_US.original_filename' => 'Sony',
                            ],
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::EQUALS, 'Sony');
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        [
                            'term' => [
                                'values.an_image-media.ecommerce.en_US.original_filename' => 'Sony',
                            ],
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
                        ['exists' => ['field' => 'values.an_image-media.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::NOT_EQUAL, 'Sony');
    }

    function it_adds_a_filter_with_operator_empty(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        [
                            'exists' => ['field' => 'values.an_image-media.ecommerce.en_US'],
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
                        ['terms' => ['attributes_for_this_level' => ['an_image']]],
                        ['terms' => ['attributes_of_ancestors' => ['an_image']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_EMPTY, null);
    }

    function it_adds_a_filter_with_operator_not_empty(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'exists' => ['field' => 'values.an_image-media.ecommerce.en_US'],
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_NOT_EMPTY, null);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $name)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony']);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'an_image',
                MediaFilter::class,
                123
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 123]);
    }

    function it_throws_an_exception_when_the_given_value_is_null(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'an_image',
                MediaFilter::class,
                null
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, null]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributePathResolver,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                MediaFilter::class
            )
        )->during('addAttributeFilter', [$name, Operators::IN_CHILDREN_LIST, 'Sony']);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_locale(
        ProposalAttributePathResolver $attributePathResolver,
        AttributeValidatorHelper $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('an_image');
        $name->getBackendType()->willReturn('media');

        $attributePathResolver->getAttributePaths($name)->willReturn(['values.an_image-media.ecommerce.en_US']);
        $attributeValidatorHelper->validateLocale($name, 'to_TO')->willThrow(\LogicException::class);
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::class
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'to_TO']);
    }
}
