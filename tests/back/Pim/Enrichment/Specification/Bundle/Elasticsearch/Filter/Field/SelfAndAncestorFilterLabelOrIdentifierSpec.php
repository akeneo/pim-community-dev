<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\SelfAndAncestorFilterLabelOrIdentifier;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PhpSpec\ObjectBehavior;

class SelfAndAncestorFilterLabelOrIdentifierSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['self_and_ancestor.label_or_identifier'], ['CONTAINS']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelfAndAncestorFilterLabelOrIdentifier::class);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_operator()
    {
        $this->getOperators()->shouldReturn(['CONTAINS']);
        $this->supportsOperator('CONTAINS')->shouldReturn(true);
        $this->supportsOperator('=')->shouldReturn(false);
    }

    function it_supports_field()
    {
        $this->supportsField('self_and_ancestor.label_or_identifier')->shouldReturn(true);
        $this->supportsField('not_supported_field')->shouldReturn(false);
    }

    function it_adds_field_filter_on_value_localizable_and_scopable(SearchQueryBuilder $sqb)
    {
        $locale = 'en_US';
        $channel = 'ecommerce';
        $value = 'shoes';

        $clauses = [
            ['wildcard' => ['ancestors.codes' => sprintf('*%s*', $value)]],
            ['wildcard' => ['identifier' => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('ancestors.labels.%s.%s', $channel, $locale) => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('label.%s.%s', $channel, $locale) => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('ancestors.labels.%s.<all_locales>', $channel) => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('label.%s.<all_locales>', $channel) => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('ancestors.labels.<all_channels>.%s', $locale) => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('label.<all_channels>.%s', $locale) => sprintf('*%s*', $value)]],
            ['wildcard' => ['ancestors.labels.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
            ['wildcard' => ['label.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
        ];

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => $clauses,
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'self_and_ancestors.label_or_identifier',
            Operators::CONTAINS,
            $value,
            $locale,
            $channel,
            []
        );
    }

    function it_adds_field_filter_on_value(SearchQueryBuilder $sqb)
    {
        $value = 'shoes';
        $clauses = [
            ['wildcard' => ['ancestors.codes' => sprintf('*%s*', $value)]],
            ['wildcard' => ['identifier' => sprintf('*%s*', $value)]],
            ['wildcard' => ['ancestors.labels.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
            ['wildcard' => ['label.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
        ];

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => $clauses,
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'self_and_ancestors.label_or_identifier',
            Operators::CONTAINS,
            $value,
            null,
            null,
            []
        );
    }

    function it_adds_field_filter_on_value_localizable(SearchQueryBuilder $sqb)
    {
        $locale = 'en_US';
        $value = 'shoes';

        $clauses = [
            ['wildcard' => ['ancestors.codes' => sprintf('*%s*', $value)]],
            ['wildcard' => ['identifier' => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('ancestors.labels.<all_channels>.%s', $locale) => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('label.<all_channels>.%s', $locale) => sprintf('*%s*', $value)]],
            ['wildcard' => ['ancestors.labels.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
            ['wildcard' => ['label.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
        ];

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => $clauses,
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'self_and_ancestors.label_or_identifier',
            Operators::CONTAINS,
            $value,
            $locale,
            null,
            []
        );
    }

    function it_adds_field_filter_on_value_scopable(SearchQueryBuilder $sqb)
    {
        $channel = 'ecommerce';
        $value = 'shoes';

        $clauses = [
            ['wildcard' => ['ancestors.codes' => sprintf('*%s*', $value)]],
            ['wildcard' => ['identifier' => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('ancestors.labels.%s.<all_locales>', $channel) => sprintf('*%s*', $value)]],
            ['wildcard' => [sprintf('label.%s.<all_locales>', $channel) => sprintf('*%s*', $value)]],
            ['wildcard' => ['ancestors.labels.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
            ['wildcard' => ['label.<all_channels>.<all_locales>' => sprintf('*%s*', $value)]],
        ];

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => $clauses,
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'self_and_ancestors.label_or_identifier',
            Operators::CONTAINS,
            $value,
            null,
            $channel,
            []
        );
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during(
            'addFieldFilter',
            [
                'self_and_ancestors.label_or_identifier',
                Operators::CONTAINS,
                'shoes',
                null,
                null,
                []
            ]
        );
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                SelfAndAncestorFilterLabelOrIdentifier::class
            )
        )->during(
            'addFieldFilter',
            [
                'self_and_ancestors.label_or_identifier',
                Operators::IN_CHILDREN_LIST,
                'shoes',
                null,
                null,
                []
            ]
        );
    }
}
