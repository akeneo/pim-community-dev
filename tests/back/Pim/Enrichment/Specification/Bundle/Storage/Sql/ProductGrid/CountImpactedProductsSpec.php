<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CountImpactedProductsSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        $this->beConstructedWith($productAndProductModelQueryBuilderFactory, $productQueryBuilderFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountImpactedProducts::class);
    }

    function it_returns_the_catalog_products_count_when_a_user_selects_all_products_in_the_grid(
        $productAndProductModelQueryBuilderFactory,
        $productQueryBuilderFactory,
        SearchQueryBuilder $sqb,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [];

        $productAndProductModelQueryBuilderFactory->create(['filters' => [
            ['field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class]
        ]])->willReturn($pqb);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $pqb->getRawFilters()->willReturn([
            [
                'field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class, 'type' => 'field', 'context' => []
            ]
        ]);
        $sqb->addFilter(Argument::any())->shouldNotBeCalled();

        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(2500);

        $productQueryBuilderFactory->create()->shouldNotBeCalled();

        $this->count($pqbFilters)->shouldReturn(2500);
    }

    function it_returns_the_catalog_products_count_when_a_user_selects_all_products_in_the_grid_with_a_label_search(
        $productAndProductModelQueryBuilderFactory,
        $productQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        SearchQueryBuilder $sqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            ['field' => 'label_or_identifier', 'operator' => 'CONTAINS', 'value' => 'something']
        ];

        $productAndProductModelQueryBuilderFactory->create(['filters' => [
            ['field' => 'self_and_ancestor.label_or_identifier', 'operator' => 'CONTAINS', 'value' => 'something'],
            ['field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class]
        ]])->willReturn($pqb);

        $pqb->getQueryBuilder()->willReturn($sqb);
        $pqb->getRawFilters()->willReturn([
            ['field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class, 'type' => 'field', 'context' => []],
            ['field' => 'self_and_ancestor.label_or_identifier', 'operator' => 'CONTAINS', 'value' => 'something', 'type' => 'field', 'context' => []],
        ]);
        $sqb->addFilter(Argument::any())->shouldNotBeCalled();

        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(2500);

        $productQueryBuilderFactory->create()->shouldNotBeCalled();

        $this->count($pqbFilters)->shouldReturn(2500);
    }

    public function it_returns_all_the_selected_products_count_when_a_user_selects_a_list_of_products(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqbForProducts,
        ProductQueryBuilderInterface $pqbForProductsInsideProductModels,
        CursorInterface $cursorForProducts,
        CursorInterface $cursorForProductsInsideProductModels
    ) {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_1', 'product_2', 'product_3'],
                'context' => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create(['filters' => $pqbFilters])->willReturn($pqbForProducts);
        $pqbForProducts->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProducts->execute()->willReturn($cursorForProducts);

        $productAndProductModelQueryBuilderFactory->create(['filters' => []])->willReturn($pqbForProductsInsideProductModels);
        $pqbForProductsInsideProductModels->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProductsInsideProductModels->addFilter('ancestor.id', Operators::IN_LIST, [])->shouldBeCalled();
        $pqbForProductsInsideProductModels->execute()->willReturn($cursorForProductsInsideProductModels);

        $cursorForProducts->count()->willReturn(3);
        $cursorForProductsInsideProductModels->count()->willReturn(0);

        $this->count($pqbFilters)->shouldReturn(3);
    }

    public function it_returns_all_the_selected_variant_products_when_a_user_selects_a_product_model(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqbForProducts,
        ProductQueryBuilderInterface $pqbForProductsInsideProductModels,
        CursorInterface $cursorForProducts,
        CursorInterface $cursorForProductsInsideProductModels
    ) {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_model_1'],
                'context' => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create(['filters' => $pqbFilters])->willReturn($pqbForProducts);
        $pqbForProducts->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProducts->execute()->willReturn($cursorForProducts);

        $productAndProductModelQueryBuilderFactory->create(['filters' => []])->willReturn($pqbForProductsInsideProductModels);
        $pqbForProductsInsideProductModels->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProductsInsideProductModels->addFilter('ancestor.id', Operators::IN_LIST, ['product_model_1'])->shouldBeCalled();
        $pqbForProductsInsideProductModels->execute()->willReturn($cursorForProductsInsideProductModels);

        $cursorForProducts->count()->willReturn(0);
        $cursorForProductsInsideProductModels->count()->willReturn(10);

        $this->count($pqbFilters)->shouldReturn(10);
    }

    public function it_returns_all_the_selected_variant_products_when_a_user_selects_product_models_and_products(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqbForProducts,
        ProductQueryBuilderInterface $pqbForProductsInsideProductModels,
        CursorInterface $cursorForProducts,
        CursorInterface $cursorForProductsInsideProductModels
    ) {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context' => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create(['filters' => $pqbFilters])->willReturn($pqbForProducts);
        $pqbForProducts->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProducts->execute()->willReturn($cursorForProducts);

        $productAndProductModelQueryBuilderFactory->create(['filters' => []])->willReturn($pqbForProductsInsideProductModels);
        $pqbForProductsInsideProductModels->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProductsInsideProductModels->addFilter('ancestor.id', Operators::IN_LIST, ['product_model_1', 'product_model_2'])->shouldBeCalled();
        $pqbForProductsInsideProductModels->execute()->willReturn($cursorForProductsInsideProductModels);

        $cursorForProducts->count()->willReturn(2);
        $cursorForProductsInsideProductModels->count()->willReturn(8);

        $this->count($pqbFilters)->shouldReturn(10);
    }

    public function it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks_products(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqbForAllProducts,
        SearchQueryBuilder $searchQueryBuilder,
        ProductQueryBuilderInterface $pqbForProducts,
        ProductQueryBuilderInterface $pqbForProductsInsideProductModels,
        CursorInterface $cursorForAllProducts,
        CursorInterface $cursorForProducts,
        CursorInterface $cursorForProductsInsideProductModels
    ) {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_1', 'product_2'],
                'context'  => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create(['filters' => [
            ['field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class]
        ]])->willReturn($pqbForAllProducts);
        $pqbForAllProducts->execute()->willReturn($cursorForAllProducts);
        $pqbForAllProducts->getQueryBuilder()->willReturn($searchQueryBuilder);
        $pqbForAllProducts->getRawFilters()->willReturn([
            ['field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class, 'type' => 'field', 'context' => []],
        ]);
        $searchQueryBuilder->addFilter(Argument::any())->shouldNotBeCalled();

        $productAndProductModelQueryBuilderFactory->create(['filters' => [
            [
                'field'    => 'id',
                'operator' => 'IN',
                'value'    => ['product_1', 'product_2'],
                'context'  => []
            ]
        ]])->willReturn($pqbForProducts);
        $pqbForProducts->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProducts->execute()->willReturn($cursorForProducts);

        $productAndProductModelQueryBuilderFactory->create(['filters' => []])->willReturn($pqbForProductsInsideProductModels);
        $pqbForProductsInsideProductModels->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProductsInsideProductModels->addFilter('ancestor.id', Operators::IN_LIST, [])->shouldBeCalled();
        $pqbForProductsInsideProductModels->execute()->willReturn($cursorForProductsInsideProductModels);

        $cursorForAllProducts->count()->willReturn(2500);
        $cursorForProducts->count()->willReturn(2);
        $cursorForProductsInsideProductModels->count()->willReturn(0);

        $this->count($pqbFilters)->shouldReturn(2498);
    }

    public function it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks_products_and_product_models(
        SearchQueryBuilder $searchQueryBuilder,
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqbForAllProducts,
        ProductQueryBuilderInterface $pqbForProducts,
        ProductQueryBuilderInterface $pqbForProductsInsideProductModels,
        CursorInterface $cursorForAllProducts,
        CursorInterface $cursorForProducts,
        CursorInterface $cursorForProductsInsideProductModels
    ) {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context'  => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create(['filters' => [
            ['field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class]
        ]])->willReturn($pqbForAllProducts);
        $pqbForAllProducts->execute()->willReturn($cursorForAllProducts);
        $pqbForAllProducts->getQueryBuilder()->willReturn($searchQueryBuilder);
        $pqbForAllProducts->getRawFilters()->willReturn([
            ['field' => 'entity_type', 'operator' => "=", 'value' => ProductInterface::class, 'type' => 'field', 'context' => []],
        ]);
        $searchQueryBuilder->addFilter(Argument::any())->shouldNotBeCalled();

        $productAndProductModelQueryBuilderFactory->create(['filters' => [
            [
                'field'    => 'id',
                'operator' => 'IN',
                'value'    => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context'  => []
            ]
        ]])->willReturn($pqbForProducts);
        $pqbForProducts->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProducts->execute()->willReturn($cursorForProducts);

        $productAndProductModelQueryBuilderFactory->create(['filters' => []])->willReturn($pqbForProductsInsideProductModels);
        $pqbForProductsInsideProductModels->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProductsInsideProductModels->addFilter('ancestor.id', Operators::IN_LIST, ['product_model_1', 'product_model_2'])->shouldBeCalled();
        $pqbForProductsInsideProductModels->execute()->willReturn($cursorForProductsInsideProductModels);

        $cursorForAllProducts->count()->willReturn(2500);
        $cursorForProducts->count()->willReturn(2);
        $cursorForProductsInsideProductModels->count()->willReturn(8);

        $this->count($pqbFilters)->shouldReturn(2490);
    }

    public function it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks_products_and_product_models_with_a_completeness_filter(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqbForAllProducts,
        SearchQueryBuilder $sqb,
        ProductQueryBuilderInterface $pqbForProducts,
        ProductQueryBuilderInterface $pqbForProductsInsideProductModels,
        CursorInterface $cursorForAllProducts,
        CursorInterface $cursorForProducts,
        CursorInterface $cursorForProductsInsideProductModels
    ) {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context'  => []
            ],
            [
                'field'    => 'completeness',
                'operator' => 'AT LEAST COMPLETE',
                'value'    => null,
                'context'  => []
            ]
        ];

        $pqbFiltersForProductsInsideProductModels = $pqbFilters;
        unset($pqbFiltersForProductsInsideProductModels[0]);

        $pqbFiltersForAllProducts = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context'  => []
            ],
            [
                'field'    => 'completeness',
                'operator' => '=',
                'value'    => 100,
                'context'  => []
            ],
            [
                'field'    => 'entity_type',
                'operator' => '=',
                'value'    => ProductInterface::class
            ],
        ];
        unset($pqbFiltersForAllProducts[0]);

        $productAndProductModelQueryBuilderFactory->create(['filters' => $pqbFiltersForAllProducts])->willReturn($pqbForAllProducts);
        $pqbForAllProducts->execute()->willReturn($cursorForAllProducts);
        $pqbForAllProducts->getQueryBuilder()->willReturn($sqb);
        $pqbForAllProducts->getRawFilters()->willReturn([
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context'  => [],
                'type'     => 'field'
            ],
            [
                'field'    => 'completeness',
                'operator' => '=',
                'value'    => 100,
                'context'  => [],
                'type' => 'field'
            ],
            [
                'field'    => 'entity_type',
                'operator' => '=',
                'value'    => ProductInterface::class,
                'type'     => 'field',
                'context'  => []
            ],
        ]);

        $sqb->addFilter(Argument::any())->shouldNotBeCalled();

        $productAndProductModelQueryBuilderFactory->create(['filters' => [
            [
                'field'    => 'id',
                'operator' => 'IN',
                'value'    => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context'  => []
            ],
            [
                'field'    => 'completeness',
                'operator' => 'AT LEAST COMPLETE',
                'value'    => null,
                'context'  => []
            ]
        ]])->willReturn($pqbForProducts);
        $pqbForProducts->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProducts->execute()->willReturn($cursorForProducts);

        $productAndProductModelQueryBuilderFactory->create(['filters' => $pqbFiltersForProductsInsideProductModels])->willReturn($pqbForProductsInsideProductModels);
        $pqbForProductsInsideProductModels->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqbForProductsInsideProductModels->addFilter('ancestor.id', Operators::IN_LIST, ['product_model_1', 'product_model_2'])->shouldBeCalled();
        $pqbForProductsInsideProductModels->execute()->willReturn($cursorForProductsInsideProductModels);

        $cursorForAllProducts->count()->willReturn(2500);
        $cursorForProducts->count()->willReturn(2);
        $cursorForProductsInsideProductModels->count()->willReturn(8);

        $this->count($pqbFilters)->shouldReturn(2490);
    }

    public function it_computes_when_a_user_selects_all_entities_with_other_filters(
        $productAndProductModelQueryBuilderFactory,
        SearchQueryBuilder $sqb,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            [
                'field' => 'color',
                'operator' => '=',
                'value' => 'red',
                'context' => []
            ],
            [
                'field' => 'size',
                'operator' => 'IN LIST',
                'value' => ['l', 'm'],
                'context' => []
            ],
        ];

        $productAndProductModelQueryBuilderFactory->create(
            [
                'filters' => [
                    [
                        'field' => 'color',
                        'operator' => '=',
                        'value' => 'red',
                        'context' => []
                    ],
                    [
                        'field' => 'size',
                        'operator' => 'IN LIST',
                        'value' => ['l', 'm'],
                        'context' => []
                    ],
                    [
                        'field'    => 'entity_type',
                        'operator' => '=',
                        'value'    => ProductInterface::class
                    ],
                ]
            ]
        )->willReturn($pqb);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $pqb->getRawFilters()->willReturn([
            [
                'field' => 'color',
                'operator' => '=',
                'value' => 'red',
                'context' => [],
                'type' => 'attribute'
            ],
            [
                'field' => 'size',
                'operator' => 'IN LIST',
                'value' => ['l', 'm'],
                'context' => [],
                'type' => 'attribute'
            ],
        ]);

        $sqb->addFilter(Argument::any())->shouldNotBeCalled();

        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(12);

        $this->count($pqbFilters)->shouldReturn(12);
    }

    public function it_adds_a_filter_on_attributes_level_when_there_are_empty_attribute_filters(
        $productAndProductModelQueryBuilderFactory,
        SearchQueryBuilder $sqb,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            [
                'field' => 'color',
                'operator' => Operators::IS_EMPTY,
                'value' => '',
                'context' => []
            ],
        ];

        $productAndProductModelQueryBuilderFactory->create(
            [
                'filters' => [
                    [
                        'field' => 'color',
                        'operator' => Operators::IS_EMPTY,
                        'value' => '',
                        'context' => []
                    ],
                    [
                        'field'    => 'entity_type',
                        'operator' => '=',
                        'value'    => ProductInterface::class
                    ],
                ]
            ]
        )->willReturn($pqb);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $pqb->getRawFilters()->willReturn([
            [
                'field' => 'color',
                'operator' => Operators::IS_EMPTY,
                'value' => '',
                'context' => [],
                'type' => 'attribute'
            ],
        ]);

        $sqb->addFilter([
            'bool' => [
                'should' => [
                    [
                        'terms' => [
                            'attributes_for_this_level' => ['color']
                        ],
                    ],
                    [
                        'terms' => [
                            'attributes_of_ancestors' => ['color']
                        ],
                    ]
                ]
            ]
        ])->shouldBeCalled();

        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(12);

        $this->count($pqbFilters)->shouldReturn(12);
    }
}
