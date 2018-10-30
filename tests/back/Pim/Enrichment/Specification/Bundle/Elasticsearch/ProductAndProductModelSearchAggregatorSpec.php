<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAndProductModelSearchAggregatorSpec extends ObjectBehavior
{
    function let(CategoryRepositoryInterface $categoryRepository)
    {
        $this->beConstructedWith($categoryRepository);
    }

    function it_can_aggregate_results_relative_to_attribute_of_ancestor(CategoryRepositoryInterface $categoryRepository, SearchQueryBuilder $searchQueryBuilder)
    {
        $rawFilters = [
            [
                'field'    => 'foo',
                'operator' => 'CONTAINS',
                'value'    => '42',
                'context'  => [],
                'type'     => 'attribute'
            ],
            [
                'field'    => 'bar',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ],
            [
                'field'    => 'baz',
                'operator' => 'EQUALS',
                'value'    => 'sku_893042',
                'context'  => [],
                'type'     => 'attribute'
            ],
        ];

        $categoryRepository->findOneBy(["code" => Argument::any()])->shouldNotBeCalled();

        $searchQueryBuilder->addMustNot([
            'bool' => [
                'filter' => [
                    [
                        'terms' => [ 'attributes_of_ancestors' => ['foo']]
                    ],
                    [
                        'terms' => [ 'attributes_of_ancestors' => ['baz']]
                    ]
                ],
            ]
        ])->shouldBeCalled();

        $this->aggregateResults($searchQueryBuilder, $rawFilters)->shouldReturn($searchQueryBuilder);
    }

    function it_aggregate_with_attribute_of_ancestor_and_categories_of_ancestors_with_IN_operator(CategoryRepositoryInterface $categoryRepository, SearchQueryBuilder $searchQueryBuilder)
    {
        $rawFilters = [
            [
                'field'    => 'foo',
                'operator' => 'EMPTY',
                'value'    => null,
                'context'  => [],
                'type'     => 'attribute',
            ],
            [
                'field'    => 'foo_currency1',
                'operator' => 'EMPTY FOR CURRENCY',
                'value'    => null,
                'context'  => [],
                'type'     => 'attribute',
            ],
            [
                'field'    => 'foo_currency2',
                'operator' => 'EMPTY ON ALL CURRENCIES',
                'value'    => null,
                'context'  => [],
                'type'     => 'attribute',
            ],
            [
                'field'    => 'bar',
                'operator' => 'IN',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field',
            ],
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['category_A'],
                'context'  => [],
                'type'     => 'field',
            ],
        ];

        $categoryRepository->findOneBy(["code" => Argument::any()])->shouldNotBeCalled();

        $searchQueryBuilder->addMustNot([
            'bool' => [
                'filter' => [
                    [
                        'terms' => ['attributes_of_ancestors' => ['foo']],
                    ],
                    [
                        'terms' => ['attributes_of_ancestors' => ['foo_currency1']],
                    ],
                    [
                        'terms' => ['attributes_of_ancestors' => ['foo_currency2']],
                    ],
                    [
                        'terms' => ['categories_of_ancestors' => ['category_A']],
                    ],
                ],
            ]
        ])->shouldBeCalled();
        $searchQueryBuilder->addFilter([
            'terms' => ['attributes_for_this_level' => ['foo', 'foo_currency1', 'foo_currency2']],
        ])->shouldBeCalled();

        $this->aggregateResults($searchQueryBuilder, $rawFilters)->shouldReturn($searchQueryBuilder);
    }


    function it_aggregate_with_attribute_of_ancestor_and_categories_of_ancestors_with_IN_CHILDREN_operator(CategoryRepositoryInterface $categoryRepository, SearchQueryBuilder $searchQueryBuilder)
    {
        $rawFilters = [
            [
                'field'    => 'foo',
                'operator' => 'EMPTY',
                'value'    => null,
                'context'  => [],
                'type'     => 'attribute',
            ],
            [
                'field'    => 'categories',
                'operator' => 'IN CHILDREN',
                'value'    => ['master_men'],
                'context'  => [],
                'type'     => 'field',
            ]
        ];

        $categoryRepository->findOneBy(["code" => 'master_men'])->shouldBeCalled();

        $searchQueryBuilder->addMustNot([
            'bool' => [
                'filter' => [
                    [
                        'terms' => ['attributes_of_ancestors' => ['foo']],
                    ],
                    [
                        'terms' => ['categories_of_ancestors' => ['master_men']],
                    ],
                ],
            ]
        ])->shouldBeCalled();
        $searchQueryBuilder->addFilter([
            'terms' => ['attributes_for_this_level' => ['foo']],
        ])->shouldBeCalled();

        $this->aggregateResults($searchQueryBuilder, $rawFilters)->shouldReturn($searchQueryBuilder);
    }

}
