<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\Operators;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(CategoryRepositoryInterface $categoryRepository)
    {
        $operators = ['IN', 'NOT IN', 'UNCLASSIFIED', 'IN OR UNCLASSIFIED', 'IN CHILDREN', 'NOT IN CHILDREN'];
        $this->beConstructedWith($categoryRepository, ['categories'], $operators);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Elasticsearch\Filter\CategoryFilter');
    }

    function it_is_a_fieldFilter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Elasticsearch\Filter\AbstractFieldFilter');
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
                'exists' => ['field' => 'categories']
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::UNCLASSIFIED, [], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_IN_LIST_OR_UNCLASSIFIED(SearchQueryBuilder $sqb) {
        $sqb->addShould(
            [
                'terms' => [
                    'categories' => ['t-shirt'],
                ],
            ]
        )->shouldBeCalled();

        $sqb->addShould(
            [
                'bool' => [
                    'must_not' => [
                        'exists' => ['field' => 'categories']
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['t-shirt'], 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'CONTAINS',
                'Pim\Bundle\CatalogBundle\Elasticsearch\Filter\CategoryFilter'
            )
        )->during('addFieldFilter', ['categories', Operators::CONTAINS, ['t-shirt']]);
    }
}
