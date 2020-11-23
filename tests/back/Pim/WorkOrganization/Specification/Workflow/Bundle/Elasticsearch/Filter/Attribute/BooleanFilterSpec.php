<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\BooleanFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(ProposalAttributePathResolver $attributePathResolver)
    {
        $this->beConstructedWith($attributePathResolver, ['pim_catalog_boolean'], ['=', '!=', 'NOT EMPTY']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            '=',
            '!=',
            'NOT EMPTY'
        ]);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('!=')->shouldReturn(true);
        $this->supportsOperator('NOT EMPTY')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributePathResolver,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributePathResolver->getAttributePaths($booleanAttribute)->willReturn([
            'values.boolean-boolean.ecommerce.en_US',
            'values.boolean-boolean.ecommerce.fr_FR'
        ]);

        $sqb->addFilter([
            'bool' => [
                'should' => [
                    ['term' => ['values.boolean-boolean.ecommerce.en_US' => true]],
                    ['term' => ['values.boolean-boolean.ecommerce.fr_FR' => true]],
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($booleanAttribute, Operators::EQUALS, true);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributePathResolver,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributePathResolver->getAttributePaths($booleanAttribute)->willReturn([
            'values.boolean-boolean.ecommerce.en_US',
            'values.boolean-boolean.ecommerce.fr_FR'
        ]);

        $sqb->addMustNot([
            'bool' => [
                'should' => [
                    ['term' => ['values.boolean-boolean.ecommerce.en_US' => false]],
                    ['term' => ['values.boolean-boolean.ecommerce.fr_FR' => false]],
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $sqb->addFilter([
            'bool' => [
                'should' => [
                    ['exists' => ['field' => 'values.boolean-boolean.ecommerce.en_US']],
                    ['exists' => ['field' => 'values.boolean-boolean.ecommerce.fr_FR']],
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($booleanAttribute, Operators::NOT_EQUAL, false);
    }

    function it_adds_a_filter_with_operator_not_empty(
        $attributePathResolver,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributePathResolver->getAttributePaths($booleanAttribute)->willReturn([
            'values.boolean-boolean.ecommerce.en_US',
            'values.boolean-boolean.ecommerce.fr_FR'
        ]);

        $sqb->addFilter([
            'bool' => [
                'should' => [
                    ['exists' => ['field' => 'values.boolean-boolean.ecommerce.en_US']],
                    ['exists' => ['field' => 'values.boolean-boolean.ecommerce.fr_FR']],
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($booleanAttribute, Operators::IS_NOT_EMPTY, false);
    }

    function it_adds_a_filter_with_operator_empty(
        $attributePathResolver,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributePathResolver->getAttributePaths($booleanAttribute)->willReturn([
            'values.boolean-boolean.ecommerce.en_US',
            'values.boolean-boolean.ecommerce.fr_FR'
        ]);

        $sqb->addMustNot([
            'bool' => [
                'should' => [
                    ['exists' => ['field' => 'values.boolean-boolean.ecommerce.en_US']],
                    ['exists' => ['field' => 'values.boolean-boolean.ecommerce.fr_FR']]
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $sqb->addFilter([
            'bool' => [
                'should' => [
                    ['terms' => ['attributes_for_this_level' => ['boolean']]],
                    ['terms' => ['attributes_of_ancestors' => ['boolean']]]
                ],
                'minimum_should_match' => 1
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($booleanAttribute, Operators::IS_EMPTY, false);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(
        AttributeInterface $booleanAttribute
    ) {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$booleanAttribute, Operators::EQUALS, false]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_boolean(
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::booleanExpected(
                'boolean',
                BooleanFilter::class,
                123
            )
        )->during('addAttributeFilter', [$booleanAttribute, Operators::EQUALS, 123]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributePathResolver,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributePathResolver->getAttributePaths($booleanAttribute)->willReturn([
            'values.boolean-boolean.ecommerce.en_US',
            'values.boolean-boolean.ecommerce.fr_FR'
        ]);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                BooleanFilter::class
            )
        )->during('addAttributeFilter', [
            $booleanAttribute,
            Operators::IN_CHILDREN_LIST,
            true
        ]);
    }
}
