<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AggregateFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

class AggregateFilterSpec extends ObjectBehavior
{
    private $rawFilters = [[
        "field" => "categories",
        "operator" => "IN CHILDREN",
        "value" => ["cameras"],
        "context" => [
            "locale" => "en_US",
            "scope" => "ecommerce",
            "limit" => 25,
            "from" => 0
        ],
        "type" => "field"
    ]];

    function let(CategoryRepositoryInterface $categoryRepository)
    {
        $this->beConstructedWith($categoryRepository, ['aggregate'], ['AGGREGATE']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregateFilter::class);
    }

    function it_is_a_fieldFilter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_categories_field()
    {
        $this->supportsField('aggregate')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'AGGREGATE',
        ]);
        $this->supportsOperator('UNCLASSIFIED')->shouldReturn(false);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_AGGREGATE(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(
            ["bool" => [
                "filter" => [
                    [
                        "terms" => [
                            "categories_of_ancestors" => ["cameras"]
                        ]
                    ]
                ]
            ]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('aggregate', Operators::AGGREGATE, ['t-shirt', 'men_short'], 'en_US', 'ecommerce', ['rawFilters' => $this->rawFilters]);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter',
            ['aggregate', Operators::AGGREGATE, null, 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_has_no_raw_filters(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'aggregate',
                'rawFilter missing',
                AggregateFilter::class,
                []
            )
        )->during('addFieldFilter', ['aggregate', Operators::AGGREGATE, null, null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb) {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'CONTAINS',
                AggregateFilter::class
            )
        )->during('addFieldFilter', ['aggregate', Operators::CONTAINS, ['t-shirt']]);
    }
}
