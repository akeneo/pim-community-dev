<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\ORM\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\CountImpactedProducts;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
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
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [];

        $productQueryBuilderFactory->create(['filters' => []])->willReturn($pqb);
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(2500);

        $productAndProductModelQueryBuilderFactory->create()->shouldNotBeCalled();

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
        $productQueryBuilderFactory,
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
                'value'    => ['product_1', 'product_2'],
                'context'  => []
            ]
        ];

        $productQueryBuilderFactory->create(['filters' => []])->willReturn($pqbForAllProducts);
        $pqbForAllProducts->execute()->willReturn($cursorForAllProducts);

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
        $productAndProductModelQueryBuilderFactory,
        $productQueryBuilderFactory,
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

        $productQueryBuilderFactory->create(['filters' => []])->willReturn($pqbForAllProducts);
        $pqbForAllProducts->execute()->willReturn($cursorForAllProducts);

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
        $productQueryBuilderFactory,
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
            ]
        ];
        unset($pqbFiltersForAllProducts[0]);

        $productQueryBuilderFactory->create(['filters' => $pqbFiltersForAllProducts])->willReturn($pqbForAllProducts);
        $pqbForAllProducts->execute()->willReturn($cursorForAllProducts);

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
        $productQueryBuilderFactory,
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
            ]
        ];

        $productQueryBuilderFactory->create(
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
                    ]
                ]
            ]
        )->willReturn($pqb);
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(12);

        $productAndProductModelQueryBuilderFactory->create()->shouldNotBeCalled();

        $this->count($pqbFilters)->shouldReturn(12);
    }
}
