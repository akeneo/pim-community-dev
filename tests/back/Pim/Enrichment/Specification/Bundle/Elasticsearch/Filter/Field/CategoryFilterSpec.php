<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\CategoryFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(CategoryRepositoryInterface $categoryRepository)
    {
        $operators = ['IN', 'NOT IN', 'UNCLASSIFIED', 'IN OR UNCLASSIFIED', 'IN CHILDREN', 'NOT IN CHILDREN'];
        $this->beConstructedWith($categoryRepository, ['categories'], $operators);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryFilter::class);
    }

    function it_is_a_fieldFilter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_categories_field()
    {
        $this->supportsField('categories')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'IN',
            'NOT IN',
            'UNCLASSIFIED',
            'IN OR UNCLASSIFIED',
            'IN CHILDREN',
            'NOT IN CHILDREN',
        ]);
        $this->supportsOperator('UNCLASSIFIED')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_IN_LIST(SearchQueryBuilder $sqb) {
        $sqb->addFilter(
            [
                'terms' => [
                    'categories' => ['t-shirt'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::IN_LIST, ['t-shirt'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_NOT_IN_LIST(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(
            [
                'terms' => [
                    'categories' => ['t-shirt'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::NOT_IN_LIST, ['t-shirt'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_IN_CHILDREN_LIST(
        $categoryRepository,
        SearchQueryBuilder $sqb,
        CategoryInterface $tShirtCategory
    ) {
        $tShirtCategory->getCode()->willReturn('t-shirt');
        $categoryRepository->getAllChildrenCodes($tShirtCategory)->willReturn(['alaiz-breizh', 'BZH']);
        $categoryRepository->findOneBy(['code' => 't-shirt'])->willReturn($tShirtCategory);
        $sqb->addFilter(
            [
                'terms' => [
                    'categories' => ['alaiz-breizh', 'BZH', 't-shirt'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::IN_CHILDREN_LIST, ['t-shirt'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_NOT_IN_CHILDREN_LIST(
        $categoryRepository,
        SearchQueryBuilder $sqb,
        CategoryInterface $tShirtCategory
    ) {
        $tShirtCategory->getCode()->willReturn('t-shirt');
        $categoryRepository->getAllChildrenCodes($tShirtCategory)->willReturn(['alaiz-breizh', 'BZH']);
        $categoryRepository->findOneBy(['code' => 't-shirt'])->willReturn($tShirtCategory);
        $sqb->addMustNot(
            [
                'terms' => [
                    'categories' => ['alaiz-breizh', 'BZH', 't-shirt'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::NOT_IN_CHILDREN_LIST, ['t-shirt'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_UNCLASSIFIED(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(
            [
                'exists' => ['field' => 'categories'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::UNCLASSIFIED, [], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_IN_LIST_OR_UNCLASSIFIED(SearchQueryBuilder $sqb) {
        $sqb->addFilter([
            'bool' => [
                'should' => [
                    [
                        'terms' => [
                            'categories' => ['t-shirt']
                        ]
                    ],
                    [
                        'bool' => [
                            'must_not' => [
                                'exists' => ['field' => 'categories']
                            ]
                        ]
                    ]
                ],
                'minimum_should_match' => 1,
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['t-shirt'], 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter',
            ['categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['t-shirt'], 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['categories', Operators::IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_not_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['categories', Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_not_in_children_list(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['categories', Operators::NOT_IN_CHILDREN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_children_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['categories', Operators::IN_CHILDREN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_list_or_unclassified(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['categories', Operators::IN_LIST_OR_UNCLASSIFIED, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_does_not_throws_an_exception_when_the_given_value_is_not_an_array_with_unclassified(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldNotThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['categories', Operators::UNCLASSIFIED, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'categories',
                CategoryFilter::class,
                false
            )
        )->during('addFieldFilter', ['categories', Operators::IN_LIST, [false], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb) {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'CONTAINS',
                CategoryFilter::class
            )
        )->during('addFieldFilter', ['categories', Operators::CONTAINS, ['t-shirt']]);
    }
}
