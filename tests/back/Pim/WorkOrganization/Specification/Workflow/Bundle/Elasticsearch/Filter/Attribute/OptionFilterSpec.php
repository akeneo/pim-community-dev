<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\OptionFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;

class OptionFilterSpec extends ObjectBehavior
{
    function let(
        ProposalAttributePathResolver $attributePathResolver,
        AttributeOptionRepository $attributeOptionRepository
    ) {
        $operators = ['IN', 'EMPTY', 'NOT_EMPTY', 'NOT IN'];
        $this->beConstructedWith(
            $attributePathResolver,
            $attributeOptionRepository,
            ['pim_catalog_simpleselect', 'pim_catalog_multiselect'],
            $operators
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionFilter::class);
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

    function it_supports_attribute_option(
        AttributeInterface $color,
        AttributeInterface $brands,
        AttributeInterface $description
    ) {
        $color->getType()->willReturn('pim_catalog_simpleselect');
        $brands->getType()->willReturn('pim_catalog_multiselect');
        $description->getType()->willReturn('pim_catalog_text');


        $this->supportsAttribute($color)->shouldReturn(true);
        $this->supportsAttribute($brands)->shouldReturn(true);
        $this->supportsAttribute($description)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_empty(
        $attributePathResolver,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.color-option.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['color']]],
                        ['terms' => ['attributes_of_ancestors' => ['color']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_EMPTY, ['black']);
    }

    function it_adds_a_filter_with_operator_in_list(
        $attributePathResolver,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([['code' => 'black']]);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['values.color-option.ecommerce.en_US' => ['black']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IN_LIST, ['black']);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributePathResolver,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.color-option.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_NOT_EMPTY, ['black']);
    }

    function it_adds_a_filter_with_operator_not_in_list(
        $attributePathResolver,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([['code' => 'black']]);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['values.color-option.ecommerce.en_US' => ['black']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.color-option.ecommerce.en_US']]
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
        $attributePathResolver,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'color',
                OptionFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, 'NOT_AN_ARRAY']);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(
        $attributePathResolver,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'color',
                OptionFilter::class,
                true
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, [true]]);
    }

    function it_throws_an_exception_when_search_values_does_not_exists(
        $attributePathResolver,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([]);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            new ObjectNotFoundException(
                sprintf(
                    'Object "%s" with code "%s" does not exist',
                    $color->getWrappedObject()->getBackendType(),
                    'black'
                )
            )
        )->during('addAttributeFilter', [$color, Operators::IN_LIST, ['black']]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributePathResolver,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([['code' => 'black']]);

        $attributePathResolver->getAttributePaths($color)->willReturn(['values.color-option.ecommerce.en_US']);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                Operators::IN_CHILDREN_LIST,
                OptionFilter::class
            )
        )->during('addAttributeFilter', [$color, Operators::IN_CHILDREN_LIST, ['black']]);
    }
}
